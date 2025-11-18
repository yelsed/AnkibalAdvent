<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarDay extends Model
{
    /** @use HasFactory<\Database\Factories\CalendarDayFactory> */
    use HasFactory;

    protected $fillable = [
        'calendar_id',
        'day_number',
        'gift_type',
        'title',
        'content_text',
        'content_image_path',
        'product_code',
        'audio_url',
        'unlocked_at',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'unlocked_at' => 'datetime',
        ];
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function isUnlocked(): bool
    {
        return $this->unlocked_at !== null;
    }

    public function canBeUnlocked(?User $user = null): bool
    {
        // If already unlocked, cannot unlock again
        if ($this->isUnlocked()) {
            return false;
        }

        // Admins can always unlock any day
        if ($user && $user->is_admin) {
            return true;
        }

        // Debug mode: allow all days to be unlocked
        if (config('app.calendar_debug_mode')) {
            return true;
        }

        $now = now();
        $currentDay = $now->day;
        $currentMonth = $now->month;
        $currentHour = $now->hour;
        $currentMinute = $now->minute;

        // Can only unlock in December
        if ($currentMonth !== 12) {
            return false;
        }

        // Can only unlock if day_number <= current day
        if ($this->day_number > $currentDay) {
            return false;
        }

        // Can only unlock if it's 07:00 or later on that day
        // If it's the current day, check if it's past 07:00
        if ($this->day_number === $currentDay) {
            // Check if current time is 07:00 or later
            if ($currentHour < 7) {
                return false;
            }
        }

        return true;
    }
}
