<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
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

        $calendar = Calendar::create($request->validated());

        // Create all 31 days for the calendar
        for ($day = 1; $day <= 31; $day++) {
            $calendar->days()->create([
                'day_number' => $day,
                'gift_type' => 'text',
                'title' => "Day {$day}",
                'content_text' => 'This gift hasn\'t been set up yet.',
            ]);
        }

        return redirect()->route('admin.calendars.index')
            ->with('success', 'Calendar created successfully!');
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
