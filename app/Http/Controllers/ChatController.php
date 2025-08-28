<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function fetchMessages(Request $request)
    {
        $query = Message::query();
        
        // If session_id is provided, filter messages for that visitor
        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        } else {
            // Otherwise, get the 20 most recent messages
            $query->latest()->take(20);
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

        $request->validate([
            'message' => 'required|string',
            'from_admin' => 'required|boolean',
            'session_id' => 'required|string', // Now required for all messages
        ]);

        $sessionId = $request->input('session_id');

        // Save message in DB
        $message = new Message();
        $message->message = $request->message;
        $message->from_admin = $request->from_admin;
        $message->session_id =$sessionId;
        $message->visitor_ip = request()->ip();
        $message->save();

        // Broadcast event
        broadcast(new ChatMessageSent(
            $message->message,
            $message->from_admin,
            $message->id,
            $sessionId
        ));

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'message' => $message->message
        ]);
    }
}

