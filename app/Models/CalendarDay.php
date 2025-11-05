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

    public function canBeUnlocked(): bool
    {
        // Debug mode: allow all days to be unlocked
        if (config('app.calendar_debug_mode')) {
            return ! $this->isUnlocked();
        }

        $currentDay = now()->day;
        $currentMonth = now()->month;

        // Can only unlock in December
        if ($currentMonth !== 12) {
            return false;
        }

        // Can only unlock if day_number <= current day AND not already unlocked
        return $this->day_number <= $currentDay && ! $this->isUnlocked();
    }
}
