<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\InviteRecipientRequest;
use App\Models\Calendar;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    use AuthorizesRequests;
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

        // If calendar exists, set recipient_id
        if ($invitation->calendar_id) {
            $invitation->calendar->update([
                'recipient_id' => $user->id,
            ]);
        }

        Auth::login($user);

        // Redirect admins to dashboard, regular users to calendars index
        if ($user->is_admin) {
            return redirect()->route('dashboard')
                ->with('success', 'Welkom! Je account is aangemaakt.');
        }

        return redirect()->route('calendars.index')
            ->with('success', 'Welkom! Je account is aangemaakt.');
    }

    /**
     * Invite a recipient to a calendar.
     */
    public function inviteRecipient(InviteRecipientRequest $request, Calendar $calendar): RedirectResponse
    {
        $validated = $request->validated();

        // Create invitation
        $invitation = Invitation::create([
            'email' => $validated['email'],
            'token' => Invitation::generateToken(),
            'calendar_id' => $calendar->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Send notification
        Notification::route('mail', $validated['email'])
            ->notify(new \App\Notifications\InvitationNotification($invitation));

        return redirect()->back()
            ->with('success', __('calendar.invitation_sent', ['email' => $validated['email']]));
    }
}
