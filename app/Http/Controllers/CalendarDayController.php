<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCalendarDayRequest;
use App\Models\CalendarDay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class CalendarDayController extends Controller
{
    use AuthorizesRequests;

    /**
     * Unlock a specific calendar day.
     */
    public function unlock(CalendarDay $calendarDay): JsonResponse
    {
        $this->authorize('view', $calendarDay->calendar);

        if ($calendarDay->isUnlocked()) {
            return response()->json([
                'message' => 'This day has already been unlocked.',
                'day' => $calendarDay,
            ]);
        }

        if (! $calendarDay->canBeUnlocked()) {
            return response()->json([
                'message' => 'This day cannot be unlocked yet.',
            ], 403);
        }

        $calendarDay->update(['unlocked_at' => now()]);

        return response()->json([
            'message' => 'Day unlocked successfully!',
            'day' => $calendarDay->fresh(),
        ]);
    }

    /**
     * Update a calendar day's content (admin only).
     */
    public function update(UpdateCalendarDayRequest $request, CalendarDay $calendarDay): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('content_image')) {
            // Delete old image if exists
            if ($calendarDay->content_image_path) {
                Storage::disk('public')->delete($calendarDay->content_image_path);
            }

            // Store new image
            $path = $request->file('content_image')->store('calendar_images', 'public');
            $data['content_image_path'] = $path;
        }

        $calendarDay->update($data);

        return back()->with('success', 'Day updated successfully!');
    }
}
