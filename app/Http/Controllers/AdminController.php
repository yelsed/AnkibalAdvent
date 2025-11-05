<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
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
