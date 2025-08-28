<?php

use Illuminate\Support\Facades\Broadcast;
//Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});

//Broadcast::channel('chat.room', function ($user) {
//    return true; // or return conditionally
//});
//
//
//Broadcast::channel('chat.visitor', function ($user = null, $ip) {
//    // Always allow (we're using IP address for anonymous visitors)
//    return true;
//});

// In your channels.php
// Removed private channel for visitors - now using public channels

Broadcast::channel('chat.admin', function ($user) {
    return $user !== null;
});
