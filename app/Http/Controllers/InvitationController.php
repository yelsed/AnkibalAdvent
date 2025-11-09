<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    /**
     * Show the invitation acceptance page.
     */
    public function accept(string $token): Response|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Deze uitnodiging is ongeldig.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'Deze uitnodiging is verlopen.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'Deze uitnodiging is al gebruikt.');
        }

        return Inertia::render('auth/AcceptInvitation', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'calendar' => $invitation->calendar ? [
                    'id' => $invitation->calendar->id,
                    'title' => $invitation->calendar->title,
                ] : null,
            ],
        ]);
    }

    /**
     * Handle the invitation acceptance.
     */
    public function store(AcceptInvitationRequest $request): RedirectResponse
    {
        $invitation = Invitation::where('token', $request->token)->first();

        if (!$invitation || $invitation->isExpired() || $invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'Deze uitnodiging is ongeldig of verlopen.');
        }

        // Check if user already exists
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);
        } else {
            // Update existing user password
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update invitation
        $invitation->update([
            'user_id' => $user->id,
            'accepted_at' => now(),
        ]);

        // If calendar exists but not linked, link it
        if ($invitation->calendar_id && !$invitation->calendar->user_id) {
            $invitation->calendar->update([
                'user_id' => $user->id,
            ]);
        }

        Auth::login($user);

        return redirect()->route('calendars.index')
            ->with('success', 'Welkom! Je account is aangemaakt.');
    }
}
