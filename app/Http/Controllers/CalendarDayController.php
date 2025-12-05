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
                'message' => __('calendar.this_day_already_unlocked'),
                'day' => $calendarDay,
            ]);
        }

        // Check if debug mode is enabled (from request or config)
        // Only accept debug_mode from request if calendar_debug_enabled is true
        $debugMode = false;
        if (config('app.calendar_debug_enabled')) {
            $debugMode = request()->boolean('debug_mode') || config('app.calendar_debug_mode');
        }

        // Debug logging
        Log::info('Attempting to unlock day', [
            'day_id' => $calendarDay->id,
            'day_number' => $calendarDay->day_number,
            'allow_early_unlock' => $calendarDay->allow_early_unlock,
            'current_day' => now()->day,
            'current_month' => now()->month,
            'can_unlock' => $calendarDay->canBeUnlocked(Auth::user(), $debugMode),
        ]);

        if (! $calendarDay->canBeUnlocked(Auth::user(), $debugMode)) {
            return response()->json([
                'message' => __('calendar.this_day_cannot_unlocked_yet'),
            ], 403);
        }

        $calendarDay->update(['unlocked_at' => now()]);

        return response()->json([
            'message' => __('calendar.day_unlocked_successfully'),
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

        // Handle audio_file_id: if set, clear audio_url; if audio_url is set, clear audio_file_id
        if (isset($data['audio_file_id']) && $data['audio_file_id']) {
            $data['audio_url'] = null;
        } elseif (isset($data['audio_url']) && $data['audio_url']) {
            $data['audio_file_id'] = null;
        }

        // Handle allow_early_unlock: ensure it's always set (checkbox sends "0" or "1" as string)
        if (!isset($data['allow_early_unlock']) || $data['allow_early_unlock'] === '0' || $data['allow_early_unlock'] === 0 || $data['allow_early_unlock'] === false) {
            $data['allow_early_unlock'] = false;
        } else {
            // Convert to boolean - checkbox sends "1" when checked
            $data['allow_early_unlock'] = (bool) $data['allow_early_unlock'];
        }
        
        Log::info('Allow early unlock value', [
            'raw' => $request->input('allow_early_unlock'),
            'processed' => $data['allow_early_unlock'],
            'day_id' => $calendarDay->id,
            'day_number' => $calendarDay->day_number,
        ]);

        // Always update, even if only image was uploaded
        $calendarDay->update($data);

        // Refresh the calendar day to ensure we have the latest data
        $calendarDay->refresh();

        return redirect()->route('admin.calendars.manage', $calendarDay->calendar)
            ->with('success', __('calendar.day_updated_successfully'));
    }
}
