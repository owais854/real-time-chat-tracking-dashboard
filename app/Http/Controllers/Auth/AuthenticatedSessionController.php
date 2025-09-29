<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Sirf agent ka status update hoga
        if ($user->role === 'agent') {
            $user->status = 'online';
            $user->save();
            return redirect()->route('agent.dashboard'); // agent dashboard
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard'); // admin dashboard
        }

        // fallback agar role match na ho
        return redirect()->route('dashboard');
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Get the authenticated user before logging out
        $user = Auth::user();

        // Logout the user
        Auth::guard('web')->logout();

        // Sirf agent ka status update hoga
        if ($user && $user->role === 'agent') {
            $user->status = 'offline';
            $user->save();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

}
