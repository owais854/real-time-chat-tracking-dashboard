<?php

namespace App\Http\Controllers;

use App\Events\VisitorJoined;
use App\Events\VisitorLeft;
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
        $agent = new Agent();
        $visitor = Visitor::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_type' => $this->getDeviceType($agent),
                'browser' => $agent->browser(),
                'os' => $agent->platform(),
                'current_url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'last_activity' => now(),
                'is_active' => true,
            ]

        );

        broadcast(new VisitorJoined($visitor))->toOthers();

        return response()->json(['ok' => true, 'id' => $visitor->id]);
    }

    // Heartbeat every ~20-30s from visitor
    public function ping(Request $request)
    {
        $sessionId = session()->getId();
        $v = Visitor::where('session_id', $sessionId)->first();
        if ($v) {
            $v->update(['last_activity' => now(), 'current_url' => $request->fullUrl(), 'is_active' => true]);
        }
        return response()->json(['ok' => true]);
    }

    // Called on page unload (best-effort)
    public function leave(Request $request)
    {
        $sessionId = session()->getId();
        $v = Visitor::where('session_id', $sessionId)->first();
        if ($v) {
            $v->update(['is_active' => false, 'last_activity' => now()]);
            broadcast(new VisitorLeft($v->id))->toOthers();
        }
        return response()->json(['ok' => true]);
    }

    // Admin: list active
    public function active()
    {
        // Prune older than 2 minute as a safeguard
        $this->pruneInternal();
        return Visitor::where('is_active', true)->orderByDesc('last_activity')->get();
    }

    // Admin: manual prune endpoint (called periodically from admin UI)
    public function prune()
    {
        $removed = $this->pruneInternal();
        return response()->json(['removed' => $removed]);
    }

    private function pruneInternal(): int
    {
        $threshold = now()->subMinutes(2);
        $expired = Visitor::where('is_active', true)->where('last_activity', '<', $threshold)->get();
        $count = 0;
        foreach ($expired as $v) {
            $v->update(['is_active' => false]);
            broadcast(new VisitorLeft($v->id))->toOthers();
            $count++;
        }
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
     * Return JSON data for visitors who have messages (grouped by visitor IP).
     */
    public function historyData(Request $request)
    {
//        dd($request->all());
        $rows = \DB::table('messages')
            ->select('visitor_ip', \DB::raw('COUNT(*) as total_messages'), \DB::raw('MAX(created_at) as last_message_at'))
            ->whereNotNull('visitor_ip')
            ->groupBy('visitor_ip')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $result = $rows->map(function($row) {
            $v = \DB::table('visitors')->where('ip_address', $row->visitor_ip)->orderBy('last_activity', 'desc')->first();
            return [
                'visitor_ip' => $row->visitor_ip,
                'total_messages' => $row->total_messages,
                'last_message_at' => $row->last_message_at,
                'last_activity' => $v->last_activity ?? null,
                'current_url' => $v->current_url ?? null,
                'session_id' => $v->session_id ?? null,
            ];
        });
//        dd($result);

        return response()->json($result);
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
