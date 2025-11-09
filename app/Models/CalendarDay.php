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

        $currentDay = now()->day;
        $currentMonth = now()->month;

        // Can only unlock in December
        if ($currentMonth !== 12) {
            return false;
        }

        // Can only unlock if day_number <= current day
        return $this->day_number <= $currentDay;
    }
}
