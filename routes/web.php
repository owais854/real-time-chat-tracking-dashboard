<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
// --- Real-time Visitor Tracking ---
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\agent\AgentController;
use App\Http\Controllers\Admin\AgentController as AdminAgentController;



Route::post('/visitor/join', [VisitorController::class, 'join'])->name('visitor.join');
Route::post('/visitor/leave', [VisitorController::class, 'leave'])->name('visitor.leave');
Route::post('/visitor/ping', [VisitorController::class, 'ping'])->name('visitor.ping');
Route::post('/visitor/inactive', [VisitorController::class, 'inactive'])->name('visitor.inactive');
Route::post('/visitor/active', [VisitorController::class, 'markActive'])->name('visitor.active');
// --- End Visitor Tracking ---


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Get agents list
    Route::get('/agents', [AgentController::class, 'getAgents'])->name('agents.list');
    // Get agent statistics - using full namespace to avoid conflict
    Route::get('/agent/stats', [\App\Http\Controllers\AgentController::class, 'getAgentStats'])->name('agent.stats');

    // Protected admin chat route
    Route::get('/admin/chat', function () {
        return view('admin-chat', ['userId' => auth()->id()]);
    })->name('admin.chat');

    // Admin visitor management routes
    Route::get('/admin/visitors/active', [VisitorController::class, 'active'])->name('admin.visitors.active');
    Route::get('/admin/visitors/all', [VisitorController::class, 'all'])->name('admin.visitors.all');
    Route::post('/admin/visitors/prune', [VisitorController::class, 'prune'])->name('admin.visitors.prune');
    Route::post('/admin/visitors/cleanup', [VisitorController::class, 'cleanup'])->name('admin.visitors.cleanup');
    Route::post('/admin/visitors/prune-now', [VisitorController::class, 'pruneNow'])->name('admin.visitors.prune-now');


//    Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
//    Route::get('/agent/my-visitors', [AgentController::class, 'myVisitors'])->name('agent.my-visitors');
    Route::get('/api/agents', [AgentController::class, 'agentsList'])->name('api.agents');
    Route::post('/agent/transfer', [AgentController::class, 'transfer'])->name('agent.transfer');
});
Route::middleware(['auth','role:agent'])->group(function () {
    Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
    Route::get('/agent/my-visitors', [AgentController::class, 'myVisitors'])->name('agent.my-visitors');

    // ... other agent routes
});



// Chat routes
Route::middleware(['web'])->group(function () {
    // Broadcast authentication routes
    Broadcast::routes(['middleware' => ['web']]);

    // Public chat routes
//    Route::get('/chat', function () {
//        return view('visitor-chat');
//    });
    Route::get('/chat', function () {
        $sessionId = session()->getId();
        $ipAddress = request()->ip();

        // Get visitor data from cache to use the correct session ID
        $visitorData = cache()->get("visitor_ip_{$ipAddress}");
        $visitorSessionId = $visitorData['session_id'] ?? $sessionId;

        // Also set a visitor_id for backward compatibility
        if (!session()->has('visitor_id')) {
            session(['visitor_id' => $visitorSessionId]);
        }

        return view('visitor-chat', [
            'visitorId' => $visitorSessionId
        ]);
    });

    // Chat API routes
    Route::get('/messages', [ChatController::class, 'fetchMessages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::get('/visitors', [ChatController::class, 'getActiveVisitors']);





});

// Agent Management Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('agents', \App\Http\Controllers\Admin\AgentController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/visitors', function () {
        return view('admin-visitors');
    })->name('admin.visitors');
});


// Authentication routes (from auth.php)


// Admin chat history and data (added by assistant)
Route::middleware('auth')->group(function () {
    Route::get('/admin/history', function () {
        return view('admin-history');
    })->name('admin.history');

    Route::get('/admin/history/data', [VisitorController::class, 'historyData'])->name('admin.history.data');
});
require __DIR__.'/auth.php';
