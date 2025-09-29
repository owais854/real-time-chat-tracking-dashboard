<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total messages count
        $totalMessages = Message::count();

        // Get active visitors count (last 5 minutes)
        $activeVisitors = Visitor::where('last_activity', '>=', now()->subMinutes(5))->count();

        // Get today's messages count
        $todayChats = Message::whereDate('created_at', today())->count();

        // Get recent messages with visitor data
        $recentMessages = Message::with('visitor')
            ->latest()
            ->paginate(10);
            
        // Get total agents count
        $totalAgents = User::where('role', 'agent')->count();

        return view('dashboard', [
            'totalMessages' => $totalMessages,
            'activeVisitors' => $activeVisitors,
            'todayChats' => $todayChats,
            'recentMessages' => $recentMessages,
            'totalAgents' => $totalAgents
        ]);
    }
}
