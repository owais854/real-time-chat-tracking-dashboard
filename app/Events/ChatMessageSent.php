<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $fromAdmin;
    public $messageId;
    public $sessionId;
    public $files;

    public function __construct($message, $fromAdmin = false, $messageId = null, $sessionId = null, $files = null)
    {
        $this->message = $message;
        $this->fromAdmin = $fromAdmin;
        $this->messageId = $messageId;
        $this->sessionId = $sessionId;
        $this->files = $files;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.admin'),
            new Channel('chat.visitor.' . $this->sessionId),
        ];
    }


//    public function broadcastAs()
//    {
//        return 'chat.message';
//    }

    public function broadcastWith()
    {
        $broadcastData = [
            'message' => $this->message,
            'fromAdmin' => $this->fromAdmin,
            'messageId' => $this->messageId,
            'sessionId' => $this->sessionId,
            'files' => $this->files,
            'file' => is_array($this->files) && count($this->files) > 0 ? $this->files[0] : null, // Backward compatibility
            'time' => now()->toDateTimeString()
        ];
        
        \Log::info('ChatMessageSent broadcastWith:', $broadcastData);
        
        return $broadcastData;
    }
}
