<?php

namespace App\Http\Controllers;

use App\Events\VisitorJoined;
use App\Events\VisitorLeft;
use App\Events\VisitorOnline;
use App\Events\VisitorOffline;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class VisitorController extends Controller
{
    // Called when a visitor opens the chat page
    public function join(Request $request)
    {
        $sessionId = session()->getId();
        $ipAddress = $request->ip();
        $agent = new Agent();

        // Use IP-based visitor ID for consistency with admin messaging
        $visitorId = 'temp_' . md5($ipAddress);

        // Check if this IP already has an active visitor
        $existingVisitor = cache()->get("visitor_ip_{$ipAddress}");

        if ($existingVisitor) {
            // Update existing visitor with new session
            $existingVisitor['last_activity'] = now();
            $existingVisitor['current_url'] = $request->fullUrl();
            $existingVisitor['is_active'] = true;
            $existingVisitor['session_id'] = $sessionId; // Update to latest session ID
            $existingVisitor['sessions'][] = $sessionId;
            $existingVisitor['session_count'] = count($existingVisitor['sessions']);
        } else {
            // Create new visitor data
            $existingVisitor = [
                'id' => $visitorId,
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $request->header('User-Agent'),
                'device_type' => $this->getDeviceType($agent),
                'browser' => $agent->browser(),
                'os' => $agent->platform(),
                'current_url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'last_activity' => now(),
                'is_active' => true,
                'sessions' => [$sessionId],
                'session_count' => 1,
            ];
        }

        // Store visitor data by IP address for consistency with admin messaging
        cache()->put("visitor_ip_{$ipAddress}", $existingVisitor, now()->addMinutes(5));
        // Also store by session ID for backward compatibility
        cache()->put("visitor_session_{$sessionId}", $existingVisitor, now()->addMinutes(5));

        // Also store session mapping for cleanup
        cache()->put("session_to_ip_{$sessionId}", $ipAddress, now()->addMinutes(5));

        // Track IP addresses for cleanup
        $visitorIPs = cache()->get('visitor_ip_list', []);
        if (!in_array($ipAddress, $visitorIPs)) {
            $visitorIPs[] = $ipAddress;
            cache()->put('visitor_ip_list', $visitorIPs, now()->addMinutes(10));
        }

        // Only broadcast if this is a new visitor (first session)
        if ($existingVisitor['session_count'] === 1) {
            \Log::info('New visitor joined', ['ip' => $ipAddress, 'session_id' => $sessionId]);
            broadcast(new VisitorOnline($existingVisitor))->toOthers();
        } else {
            \Log::info('Visitor opened new tab', ['ip' => $ipAddress, 'session_id' => $sessionId, 'total_tabs' => $existingVisitor['session_count']]);
        }

        return response()->json(['ok' => true, 'id' => $existingVisitor['id'], 'session_count' => $existingVisitor['session_count']]);
    }

    // Heartbeat every ~20-30s from visitor - only updates activity, not online status
    public function ping(Request $request)
    {
        $sessionId = session()->getId();
        $ipAddress = $request->ip();

        // Update visitor data by IP address (primary)
        $visitorData = cache()->get("visitor_ip_{$ipAddress}");
        if ($visitorData) {
            $visitorData['last_activity'] = now();
            $visitorData['current_url'] = $request->fullUrl();
            $visitorData['session_id'] = $sessionId; // Update to latest session ID
            cache()->put("visitor_ip_{$ipAddress}", $visitorData, now()->addMinutes(5));
            // Also update by session for backward compatibility
            cache()->put("visitor_session_{$sessionId}", $visitorData, now()->addMinutes(5));
        }

        // Also update DB visitor if it exists (for visitors who have sent messages)
        $v = Visitor::where('session_id', $sessionId)->first();
        if ($v) {
            $v->update(['last_activity' => now(), 'current_url' => $request->fullUrl()]);
        }

        return response()->json(['ok' => true]);
    }

    // Called on page unload (best-effort) - immediately mark as offline
    public function leave(Request $request)
    {
        $sessionId = session()->getId();
        $ipAddress = $request->ip();

        // Get the visitor data by IP address
        $visitorData = cache()->get("visitor_ip_{$ipAddress}");
        if ($visitorData) {
            // Remove this session from the visitor's sessions
            $visitorData['sessions'] = array_filter($visitorData['sessions'], function($s) use ($sessionId) {
                return $s !== $sessionId;
            });
            $visitorData['session_count'] = count($visitorData['sessions']);

            if ($visitorData['session_count'] === 0) {
                // No more sessions, visitor is completely offline
                \Log::info('Visitor completely left', ['ip' => $ipAddress, 'session_id' => $sessionId]);
                broadcast(new VisitorOffline($visitorData))->toOthers();
                cache()->forget("visitor_ip_{$ipAddress}");

                // Remove from IP list
                $visitorIPs = cache()->get('visitor_ip_list', []);
                $visitorIPs = array_filter($visitorIPs, function($ip) use ($ipAddress) {
                    return $ip !== $ipAddress;
                });
                cache()->put('visitor_ip_list', $visitorIPs, now()->addMinutes(10));
            } else {
                // Still has other sessions open, just update the count
                \Log::info('Visitor closed tab', ['ip' => $ipAddress, 'session_id' => $sessionId, 'remaining_tabs' => $visitorData['session_count']]);
                cache()->put("visitor_ip_{$ipAddress}", $visitorData, now()->addMinutes(5));
            }
        }

        // Clean up session mapping
        cache()->forget("session_to_ip_{$sessionId}");

        // Update DB visitor if it exists (for visitors who have sent messages)
        $v = Visitor::where('session_id', $sessionId)->first();
        if ($v) {
            \Log::info('Visitor left (DB)', ['session_id' => $sessionId, 'visitor_id' => $v->id]);
            $v->update(['is_active' => false, 'last_activity' => now()]);
            broadcast(new VisitorLeft($v->id))->toOthers();
            // Create visitor data for offline event
            $visitorData = [
                'id' => $v->id,
                'ip_address' => $v->ip_address,
                'session_id' => $v->session_id,
                'last_activity' => $v->last_activity
            ];
            broadcast(new VisitorOffline($visitorData))->toOthers();
        }

        return response()->json(['ok' => true]);
    }

    // Admin: list active
    public function active()
    {
        // Prune older than 1 minute as a safeguard
        $this->pruneInternal();

        // Get visitors from database (who have sent messages) - only truly active ones
        $dbVisitors = Visitor::where('is_active', true)
            ->where('last_activity', '>=', now()->subMinutes(1))
            ->orderByDesc('last_activity')
            ->get();

        // Get visitors from cache (who are just browsing) - only recent ones
        $cachedVisitors = [];
        $visitorIPs = cache()->get('visitor_ip_list', []);

        // Track IP addresses that are already in the database to avoid duplicates
        $dbVisitorIPs = $dbVisitors->pluck('ip_address')->filter()->toArray();

        foreach ($visitorIPs as $ipAddress) {
            $visitorData = cache()->get("visitor_ip_{$ipAddress}");
            if ($visitorData && $visitorData['is_active'] && !in_array($ipAddress, $dbVisitorIPs)) {
                // Only include if they've been active in the last minute AND not already in database
                $lastActivity = is_string($visitorData['last_activity'])
                    ? \Carbon\Carbon::parse($visitorData['last_activity'])
                    : $visitorData['last_activity'];

                if ($lastActivity >= now()->subMinutes(1)) {
                    // Add session count info to the visitor data
                    $visitorData['tabs_count'] = $visitorData['session_count'];
                    $cachedVisitors[] = (object) $visitorData; // Convert to object to match DB format
                }
            }
        }

        // Combine and sort by last activity
        $allVisitors = collect($dbVisitors)->merge($cachedVisitors)
            ->sortByDesc('last_activity')
            ->values();

        return $allVisitors;
    }

    // Admin: list all visitors (active and inactive)
    public function all()
    {

        // Get ALL visitors from database (both active and inactive)
        $dbVisitors = Visitor::orderByDesc('last_activity')->get();

        // Get visitors from cache (who are just browsing) - include all cached ones
        $cachedVisitors = [];
        $visitorIPs = cache()->get('visitor_ip_list', []);

        // Track IP addresses that are already in the database to avoid duplicates
        $dbVisitorIPs = $dbVisitors->pluck('ip_address')->filter()->toArray();

        foreach ($visitorIPs as $ipAddress) {
            $visitorData = cache()->get("visitor_ip_{$ipAddress}");
            if ($visitorData && !in_array($ipAddress, $dbVisitorIPs)) {
                // Only add cached visitors that are NOT already in the database
                // Add session count info to the visitor data
                $visitorData['tabs_count'] = $visitorData['session_count'];
                $cachedVisitors[] = (object) $visitorData; // Convert to object to match DB format
            }
        }

        // Combine and sort by last activity
        $allVisitors = collect($dbVisitors)->merge($cachedVisitors)
            ->sortByDesc(function($visitor) {
                return $visitor->last_activity ?? $visitor->created_at ?? now();
            })
            ->values();

        return $allVisitors;
    }

    // Admin: manual prune endpoint (called periodically from admin UI)
    public function prune()
    {
        $removed = $this->pruneInternal();
        return response()->json(['removed' => $removed]);
    }

    // Admin: force cleanup all old visitors
    public function cleanup()
    {
        // Mark all old visitors as inactive
        $oldVisitors = Visitor::where('last_activity', '<', now()->subMinutes(1))->get();
        foreach ($oldVisitors as $visitor) {
            $visitor->update(['is_active' => false]);
            broadcast(new VisitorOffline($visitor->id))->toOthers();
        }

        // Clear all cached visitors
        $visitorIPs = cache()->get('visitor_ip_list', []);
        foreach ($visitorIPs as $ipAddress) {
            $visitorData = cache()->get("visitor_ip_{$ipAddress}");
            if ($visitorData) {
                broadcast(new VisitorOffline($visitorData['id']))->toOthers();
            }
            cache()->forget("visitor_ip_{$ipAddress}");
        }
        cache()->forget('visitor_ip_list');

        return response()->json(['cleaned' => $oldVisitors->count() + count($visitorIPs)]);
    }

    // Manual pruning for immediate testing
    public function pruneNow()
    {
        $pruned = $this->pruneInternal();
        return response()->json(['pruned' => $pruned]);
    }

    private function pruneInternal(): int
    {
        $threshold = now()->subMinutes(1); // 1 minute threshold to match frontend
        $count = 0;

        // Prune DB visitors who haven't been active recently
        $expired = Visitor::where('is_active', true)->where('last_activity', '<', $threshold)->get();
        foreach ($expired as $v) {
            $v->update(['is_active' => false]);
            broadcast(new VisitorLeft($v->id))->toOthers();
            broadcast(new VisitorOffline($v->id))->toOthers();
            $count++;
        }

        // Prune cached visitors who haven't pinged recently
        $visitorIPs = cache()->get('visitor_ip_list', []);
        $activeIPs = [];

        foreach ($visitorIPs as $ipAddress) {
            $visitorData = cache()->get("visitor_ip_{$ipAddress}");
            if ($visitorData) {
                $lastActivity = is_string($visitorData['last_activity'])
                    ? \Carbon\Carbon::parse($visitorData['last_activity'])
                    : $visitorData['last_activity'];

                if ($lastActivity < $threshold) {
                    // Remove expired cached visitor
                    cache()->forget("visitor_ip_{$ipAddress}");
                    broadcast(new VisitorOffline($visitorData['id']))->toOthers();
                    $count++;
                } else {
                    $activeIPs[] = $ipAddress;
                }
            }
        }

        // Update IP list
        cache()->put('visitor_ip_list', $activeIPs, now()->addMinutes(10));

        return $count;
    }


    /**
     * Return the admin history view (list of visitors who have chatted).
     */
    public function history()
    {
        return view('admin-history');
    }

    /**
     * Return JSON data for all visitors (both those with and without messages).
     */
    public function historyData(Request $request)
    {
        // Get all visitors from database
        $visitors = \DB::table('visitors')
            ->orderBy('last_activity', 'desc')
            ->get();

        // Get message counts for each visitor
        $messageCounts = \DB::table('messages')
            ->select('visitor_ip', \DB::raw('COUNT(*) as total_messages'), \DB::raw('MAX(created_at) as last_message_at'))
            ->whereNotNull('visitor_ip')
            ->groupBy('visitor_ip')
            ->get()
            ->keyBy('visitor_ip');

        $result = $visitors->map(function($visitor) use ($messageCounts) {
            $messageData = $messageCounts->get($visitor->ip_address);

            // Check if visitor is currently online (from cache or database)
            $isOnline = false;
            $lastActivity = $visitor->last_activity ?? null;

            // Check database visitor status
            if ($visitor->is_active) {
                $isOnline = true;
            }

            // Check cached visitor status (for visitors who haven't sent messages yet)
            $cachedVisitor = cache()->get("visitor_ip_{$visitor->ip_address}");
            if ($cachedVisitor) {
                $cachedLastActivity = is_string($cachedVisitor['last_activity'])
                    ? \Carbon\Carbon::parse($cachedVisitor['last_activity'])
                    : $cachedVisitor['last_activity'];

                // If cached visitor is more recent than DB visitor, use cached data
                if (!$lastActivity || $cachedLastActivity > \Carbon\Carbon::parse($lastActivity)) {
                    $lastActivity = $cachedLastActivity->toDateTimeString();
                    $isOnline = $cachedLastActivity > now()->subMinutes(1); // 1 minute threshold
                }
            }

            return [
                'visitor_ip' => $visitor->ip_address,
                'total_messages' => $messageData->total_messages ?? 0,
                'last_message_at' => $messageData->last_message_at ?? null,
                'last_activity' => $lastActivity,
                'current_url' => $visitor->current_url ?? $cachedVisitor['current_url'] ?? null,
                'session_id' => $visitor->session_id ?? $cachedVisitor['session_id'] ?? null,
                'is_active' => $isOnline,
            ];
        });

        return response()->json($result);
    }

    // Called when visitor becomes inactive (switches tabs, minimizes browser)
    public function inactive(Request $request)
    {
        $sessionId = session()->getId();
        $ipAddress = $request->ip();

        // Get cached visitor data
        $cachedVisitor = cache()->get("visitor_ip_{$ipAddress}");

        if ($cachedVisitor) {
            // Update visitor status to inactive
            $cachedVisitor['is_active'] = false;
            $cachedVisitor['last_activity'] = now();
            $cachedVisitor['status'] = 'inactive';

            // Cache the updated visitor data
            cache()->put("visitor_ip_{$ipAddress}", $cachedVisitor, 3600); // 1 hour

            // Also update database if visitor exists
            $visitor = Visitor::where('ip_address', $ipAddress)->first();

            if ($visitor) {
                $visitor->update([
                    'is_active' => false,
                    'last_activity' => now()
                ]);
            }

            // Broadcast visitor offline event with complete visitor data
            broadcast(new VisitorOffline($cachedVisitor));

            \Log::info('Visitor marked as inactive', [
                'ip' => $ipAddress,
                'session_id' => $sessionId,
                'status' => 'inactive'
            ]);
        }

        return response()->json(['status' => 'inactive', 'ok' => true]);
    }

    // Called when visitor becomes active again (returns to chat tab)
    public function markActive(Request $request)
    {
        $sessionId = session()->getId();
        $ipAddress = $request->ip();

        // Get cached visitor data
        $cachedVisitor = cache()->get("visitor_ip_{$ipAddress}");

        if ($cachedVisitor) {
            // Update visitor status to active
            $cachedVisitor['is_active'] = true;
            $cachedVisitor['last_activity'] = now();
            $cachedVisitor['status'] = 'active';

            // Cache the updated visitor data
            cache()->put("visitor_ip_{$ipAddress}", $cachedVisitor, 3600); // 1 hour

            // Also update database if visitor exists
            $visitor = Visitor::where('ip_address', $ipAddress)->first();

            if ($visitor) {
                $visitor->update([
                    'is_active' => true,
                    'last_activity' => now()
                ]);
            } else {
                // Create visitor record if it doesn't exist
                $agent = new Agent();
                Visitor::create([
                    'session_id' => $sessionId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $request->header('User-Agent'),
                    'device_type' => $this->getDeviceType($agent),
                    'browser' => $agent->browser(),
                    'os' => $agent->platform(),
                    'current_url' => $request->fullUrl(),
                    'referrer' => $request->headers->get('referer'),
                    'last_activity' => now(),
                    'is_active' => true,
                ]);
            }

            // Broadcast visitor online event with complete visitor data
            broadcast(new VisitorOnline($cachedVisitor));

            \Log::info('Visitor marked as active', [
                'ip' => $ipAddress,
                'session_id' => $sessionId,
                'status' => 'active'
            ]);
        }

        return response()->json(['status' => 'active', 'ok' => true]);
    }

private function getDeviceType($agent)
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
}
