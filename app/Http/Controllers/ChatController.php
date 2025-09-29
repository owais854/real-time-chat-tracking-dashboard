<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Message;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class ChatController extends Controller
{
    public function fetchMessages(Request $request)
    {
        $query = Message::query();
        
        // If session_id is provided, filter messages for that visitor
        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        // If visitor_ip is provided, filter messages for that visitor's IP
        elseif ($request->has('visitor_ip')) {
            $query->where('visitor_ip', $request->visitor_ip);
        } else {
            // For visitor chat (no parameters), filter by current visitor's IP
            $visitorIp = $request->ip();
            $query->where('visitor_ip', $visitorIp);
        }
        
        return $query->get()->reverse()->values();
    }

    public function getActiveVisitors()
    {
        $visitors = Message::select('session_id')
            ->distinct()
            ->whereNotNull('session_id')
            ->get()
            ->pluck('session_id');

        return response()->json($visitors);
    }

    public function sendMessage(Request $request)
    {
        // Debug logging
        \Log::info('Send message request data:', [
            'message' => $request->input('message'),
            'from_admin' => $request->input('from_admin'),
            'session_id' => $request->input('session_id'),
            'has_files' => $request->hasFile('files'),
            'has_file' => $request->hasFile('file'),
            'files_count' => $request->hasFile('files') ? count($request->file('files')) : 0,
            'all_input' => $request->all()
        ]);

        try {
            $validator = \Validator::make($request->all(), [
                'message' => 'nullable|string',
                'from_admin' => 'required|in:true,false,1,0',
                'session_id' => 'required|string',
                'files.*' => 'nullable|file|max:10240', // 10MB max per file, support multiple files
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $validator->errors()->toArray()
                ], 422);
            }
        } catch (\Exception $e) {
            \Log::error('Validation exception:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Validation exception: ' . $e->getMessage()
            ], 422);
        }

        // Custom validation: either message or files must be provided
        $hasFiles = $request->hasFile('files') || $request->hasFile('file');
        if (empty($request->input('message')) && !$hasFiles) {
            return response()->json([
                'error' => 'Either a message or a file must be provided'
            ], 422);
        }

        // Manual file type validation for multiple files
        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            // Ensure it's always an array
            if (!is_array($files)) {
                $files = [$files];
            }
            \Log::info('Files from files[] input:', ['count' => count($files), 'files' => array_map(function($f) { return $f->getClientOriginalName(); }, $files)]);
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
            \Log::info('Files from file input:', ['count' => count($files), 'files' => array_map(function($f) { return $f->getClientOriginalName(); }, $files)]);
        }
        
        \Log::info('Final files array:', ['count' => count($files), 'files' => $files]);
        
        if (!empty($files)) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            
            foreach ($files as $file) {
                $fileExtension = strtolower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
                
                if (!in_array($mimeType, $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
                    \Log::error('Invalid file type:', [
                        'mime_type' => $mimeType,
                        'extension' => $fileExtension,
                        'filename' => $file->getClientOriginalName()
                    ]);
                    return response()->json([
                        'error' => 'Invalid file type. Only images (JPG, PNG, GIF) and PDF files are allowed.'
                    ], 422);
                }
            }
        }

        // Convert string boolean to actual boolean
        $fromAdmin = filter_var($request->input('from_admin'), FILTER_VALIDATE_BOOLEAN);
        
        $sessionId = $request->input('session_id');
        $visitorIp = request()->ip();

        // If this is a visitor message (not from admin), create/update visitor record
        if (!$fromAdmin) {
            $this->createOrUpdateVisitor($sessionId, $request);
        } else {
            // If this is an admin message and session_id looks like an IP address,
            // we need to find the actual session_id for this IP
            if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $sessionId)) {
                // Find a visitor with this IP address
                $visitor = \App\Models\Visitor::where('ip_address', $sessionId)
                    ->where('is_active', true)
                    ->orderBy('last_activity', 'desc')
                    ->first();
                
                if ($visitor) {
                    $sessionId = $visitor->session_id;
                } else {
                    // If no visitor found, try to find from cache
                    $visitorData = cache()->get("visitor_ip_{$sessionId}");
                    if ($visitorData && !empty($visitorData['sessions'])) {
                        $sessionId = $visitorData['sessions'][0]; // Use first session
                    }
                }
            }
        }

        // Handle multiple file uploads if present
        $filePaths = [];
        \Log::info('About to process files:', ['files_count' => count($files), 'files' => $files]);
        if (!empty($files)) {
            foreach ($files as $file) {
                \Log::info('Processing file:', ['name' => $file->getClientOriginalName(), 'size' => $file->getSize(), 'type' => $file->getMimeType()]);
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('chat_files', $fileName, 'public');
                $filePaths[] = $filePath;
                \Log::info('File stored:', ['path' => $filePath]);
            }
        } else {
            \Log::info('No files to process');
        }

        // Save message in DB
        $message = new Message();
        $message->message = $request->input('message') ?: '';
        $message->from_admin = $fromAdmin;
        $message->session_id = $sessionId;
        $message->visitor_ip = $visitorIp;
        $message->files = !empty($filePaths) ? $filePaths : null;
        $message->save();

        // Broadcast event
        \Log::info('Broadcasting message:', [
            'message' => $message->message,
            'from_admin' => $message->from_admin,
            'message_id' => $message->id,
            'session_id' => $sessionId,
            'file_paths' => $filePaths
        ]);
        
        try {
            $event = new ChatMessageSent(
                $message->message,
                $message->from_admin,
                $message->id,
                $sessionId,
                $filePaths
            );
            \Log::info('Created ChatMessageSent event:', [
                'event_files' => $event->files,
                'file_paths_count' => count($filePaths),
                'file_paths' => $filePaths
            ]);
            broadcast($event);
            \Log::info('Successfully broadcasted ChatMessageSent event');
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast ChatMessageSent event:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'message' => $message->message,
            'file_paths' => $filePaths
        ]);
    }

    private function createOrUpdateVisitor($sessionId, $request)
    {
        $ipAddress = $request->ip();
        
        // Get visitor data from cache if available
        $cachedVisitor = cache()->get("visitor_ip_{$ipAddress}");
        
        if ($cachedVisitor) {
            // Update or create visitor record from cached data using IP address as unique identifier
            $visitor = Visitor::updateOrCreate(
                ['ip_address' => $ipAddress], // Use IP address as unique identifier
                [
                    'session_id' => $sessionId, // Update to latest session ID
                    'user_agent' => $cachedVisitor['user_agent'],
                    'device_type' => $cachedVisitor['device_type'],
                    'browser' => $cachedVisitor['browser'],
                    'os' => $cachedVisitor['os'],
                    'current_url' => $cachedVisitor['current_url'],
                    'referrer' => $cachedVisitor['referrer'],
                    'last_activity' => now(),
                    'is_active' => true,
                ]
            );
        } else {
            // Create visitor record with current request data using IP address as unique identifier
            $agent = new Agent();
            $visitor = Visitor::updateOrCreate(
                ['ip_address' => $ipAddress], // Use IP address as unique identifier
                [
                    'session_id' => $sessionId,
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
        }
        
        return $visitor;
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

