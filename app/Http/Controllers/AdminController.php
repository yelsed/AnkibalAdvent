<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Models\Calendar;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Show all calendars for admin management.
     */
    public function index(): Response
    {
        Gate::authorize('admin');

        $calendars = Calendar::with('user')->latest()->get();

        return Inertia::render('Admin/Calendars', [
            'calendars' => $calendars,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Store a new calendar assigned to a user.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        Gate::authorize('admin');

        $user = null;

        // If email is provided, create or find user and send invitation
        if ($request->email) {
            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->name ?? explode('@', $request->email)[0],
                    'password' => bcrypt(str()->random(32)), // Temporary password, will be set via invitation
                ]
            );
        } else {
            // Use existing user
            $user = User::findOrFail($request->user_id);
        }

        // Create calendar
        $calendar = Calendar::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'year' => $request->year,
            'description' => $request->description,
            'theme_color' => $request->theme_color ?? '#ec4899',
            'audio_url' => $request->audio_url,
        ]);

        // Create all 31 days for the calendar
        for ($day = 1; $day <= 31; $day++) {
            $calendar->days()->create([
                'day_number' => $day,
                'gift_type' => 'text',
                'title' => "Day {$day}",
                'content_text' => 'This gift hasn\'t been set up yet.',
            ]);
        }

        // Send invitation if email was provided (new user or existing user without password)
        if ($request->email) {
            $invitation = Invitation::create([
                'email' => $user->email,
                'token' => Invitation::generateToken(),
                'user_id' => $user->id,
                'calendar_id' => $calendar->id,
                'expires_at' => now()->addDays(7),
            ]);

            Notification::route('mail', $user->email)
                ->notify(new \App\Notifications\InvitationNotification($invitation));
        }

        $message = $request->email
            ? 'Kalender aangemaakt en uitnodiging verstuurd naar ' . $user->email
            : 'Kalender aangemaakt!';

        return redirect()->route('admin.calendars.index')
            ->with('success', $message);
    }

    /**
     * Show the admin management page for a calendar.
     */
    public function manageCalendar(Calendar $calendar): Response
    {
        Gate::authorize('admin');

        $calendar->load(['days' => function ($query) {
            $query->orderBy('day_number');
        }]);

        return Inertia::render('Admin/CalendarDays', [
            'calendar' => $calendar,
        ]);
    }
}
