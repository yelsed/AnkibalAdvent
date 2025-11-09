<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCalendarDayRequest;
use App\Models\CalendarDay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if (! $calendarDay->canBeUnlocked(Auth::user())) {
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
        // Debug: check what's coming in
        Log::info('Update request received', [
            'has_file' => $request->hasFile('content_image'),
            'all_files' => $request->allFiles(),
            'all_input' => $request->all(),
        ]);

        // Use validated() to ensure only valid data is used
        $data = $request->validated();

        // Handle image upload - this must be done separately to ensure it's always processed
        if ($request->hasFile('content_image')) {
            $file = $request->file('content_image');

            Log::info('File received', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            // Delete old image if exists
            if ($calendarDay->content_image_path) {
                Storage::disk('public')->delete($calendarDay->content_image_path);
            }

            // Store new image
            $path = $file->store('calendar_images', 'public');
            $data['content_image_path'] = $path;

            Log::info('Image stored', ['path' => $path]);
        } else {
            Log::warning('No file received in request');
        }

        // Remove content_image from data array as it's not a database field
        unset($data['content_image']);

        // Always update, even if only image was uploaded
        $calendarDay->update($data);

        // Refresh the calendar day to ensure we have the latest data
        $calendarDay->refresh();

        return redirect()->route('admin.calendars.manage', $calendarDay->calendar)
            ->with('success', 'Day updated successfully!');
    }
}
