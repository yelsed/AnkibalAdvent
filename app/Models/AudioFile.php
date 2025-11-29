<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudioFile extends Model
{
    /** @use HasFactory<\Database\Factories\AudioFileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function calendarDays(): HasMany
    {
        return $this->hasMany(CalendarDay::class);
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . $this->file_path;
    }
}
