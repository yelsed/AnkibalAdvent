<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect admins to dashboard, regular users to calendars
        $user = $request->user();
        $intended = $request->session()->pull('url.intended');

        // Only use intended URL if it's safe for this user
        if ($intended) {
            // If intended is dashboard and user is not admin, redirect to calendars instead
            if (str_contains($intended, '/dashboard') && !$user->is_admin) {
                return redirect()->route('calendars.index');
            }

            // If intended is calendars and user is admin, allow it (admins can view calendars too)
            return redirect($intended);
        }

        if ($user->is_admin) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('calendars.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
