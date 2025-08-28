<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $fromAdmin;
    public $messageId;
    public $sessionId;

    public function __construct($message, $fromAdmin = false, $messageId = null, $sessionId = null)
    {
        $this->message = $message;
        $this->fromAdmin = $fromAdmin;
        $this->messageId = $messageId;
        $this->sessionId = $sessionId;
    }

    public function broadcastOn()
    {
        if ($this->fromAdmin) {
            // Admin message goes to specific visitor's channel
            return new Channel('chat.visitor.' . $this->sessionId);
        } else {
            // Visitor message goes to admin channel with visitor info
            return new PrivateChannel('chat.admin');
        }
    }


//    public function broadcastAs()
//    {
//        return 'chat.message';
//    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'fromAdmin' => $this->fromAdmin,
            'messageId' => $this->messageId,
            'sessionId' => $this->sessionId,
            'time' => now()->toDateTimeString()
        ];
    }
}
