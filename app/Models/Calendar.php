<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    /** @use HasFactory<\Database\Factories\CalendarFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'recipient_id',
        'title',
        'year',
        'description',
        'theme_color',
        'audio_url',
        'theme_type',
        'secondary_color',
        'seasonal_config',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'seasonal_config' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Backward compatibility: user() returns owner()
     */
    public function user(): BelongsTo
    {
        return $this->owner();
    }

    public function days(): HasMany
    {
        return $this->hasMany(CalendarDay::class);
    }
}
