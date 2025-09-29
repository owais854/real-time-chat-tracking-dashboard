<?php

use Illuminate\Support\Facades\Broadcast;

// Admin private channel for chat messages
Broadcast::channel('chat.admin', function ($user) {
    return $user !== null;
});

// Presence channel for tracking online visitors (admin only)
Broadcast::channel('visitors.presence', function ($user) {
    return $user !== null;
});

// Public channel for visitor presence updates (accessible to all)
Broadcast::channel('visitors.public', function () {
    return true;
});
