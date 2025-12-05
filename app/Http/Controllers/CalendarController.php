<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarRequest;
use App\Models\Calendar;
use App\Models\Invitation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
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

        $user = auth()->user();
        // Get both calendars owned by user and calendars where user is recipient
        $calendars = Calendar::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere('recipient_id', $user->id);
        })
            ->latest()
            ->get();

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
        $validated['owner_id'] = auth()->user()->id;
        $calendar = Calendar::create($validated);

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
        }, 'days.audioFile', 'owner', 'recipient', 'user']); // user for backward compatibility

        $user = auth()->user();
        $isAdmin = $user->is_admin;
        $isOwner = $user->id === $calendar->owner_id;
        $isRecipient = $user->id === $calendar->recipient_id;

        $activeInvitation = Invitation::query()
            ->where('calendar_id', $calendar->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('expires_at')
            ->first();

        $invitationAcceptUrl = $activeInvitation
            ? route('invitations.accept', ['token' => $activeInvitation->token], absolute: true)
            : null;

        return Inertia::render('Calendars/Show', [
            'calendar' => $calendar,
            'canManage' => $isAdmin || $isOwner,
            'isAdmin' => $isAdmin,
            'isOwner' => $isOwner,
            'isRecipient' => $isRecipient,
            'invitationAcceptUrl' => $invitationAcceptUrl,
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

    /**
     * Export calendar to PDF.
     */
    public function exportPdf(Calendar $calendar): HttpResponse
    {
        $this->authorize('view', $calendar);

        $calendar->load(['days' => function ($query) {
            $query->orderBy('day_number');
        }, 'owner', 'recipient']);

        // Filter days that have content (text or image)
        $daysWithContent = $calendar->days->filter(function ($day) {
            return ($day->content_text && $day->content_text !== __('calendar.gift_hasnt_setup'))
                || $day->content_image_path
                || $day->gift_type === 'product';
        });

        $pdf = Pdf::loadView('calendars.export-pdf', [
            'calendar' => $calendar,
            'days' => $daysWithContent,
        ]);

        $filename = sprintf(
            '%s-%d.pdf',
            str_replace(' ', '-', strtolower($calendar->title)),
            $calendar->year
        );

        return $pdf->download($filename);
    }
}
