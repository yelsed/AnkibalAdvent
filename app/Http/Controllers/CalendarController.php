<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Models\Calendar;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Calendar::class);

        $calendars = auth()->user()->calendars()->latest()->get();

        return Inertia::render('Calendars/Index', [
            'calendars' => $calendars,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarRequest $request): RedirectResponse
    {
        $this->authorize('create', Calendar::class);

        $validated = $request->validated();
        $calendar = auth()->user()->calendars()->create($validated);

        // Create all 31 days for the calendar
        for ($day = 1; $day <= 31; $day++) {
            $calendar->days()->create([
                'day_number' => $day,
                'gift_type' => 'text',
                'title' => __('calendar.day_number', ['number' => $day]),
                'content_text' => __('calendar.gift_hasnt_setup'),
            ]);
        }

        return redirect()->route('calendars.show', $calendar)
            ->with('success', __('calendar.calendar_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendar $calendar): Response
    {
        $this->authorize('view', $calendar);

        $calendar->load(['days' => function ($query) {
            $query->orderBy('day_number');
        }, 'days.audioFile', 'user']);

        $isAdmin = auth()->user()->is_admin;
        $isOwner = auth()->user()->id === $calendar->user_id;

        return Inertia::render('Calendars/Show', [
            'calendar' => $calendar,
            'canManage' => $isAdmin,
            'isAdmin' => $isAdmin,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendar $calendar): RedirectResponse
    {
        $this->authorize('delete', $calendar);

        $calendar->delete();

        return redirect()->route('calendars.index')
            ->with('success', __('calendar.calendar_deleted_successfully'));
    }
}
