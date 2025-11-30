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
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(CalendarDay::class);
    }
}
