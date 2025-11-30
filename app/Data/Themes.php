<?php

namespace App\Data;

class Themes
{
    public static function all(): array
    {
        return [
            'sinterklaas' => [
                'name' => 'Sinterklaas',
                'primary' => '#dc2626',   // rood
                'secondary' => '#fbbf24', // goud
                'description' => 'Rood en goud voor Sinterklaas',
            ],
            'kerst' => [
                'name' => 'Kerst',
                'primary' => '#dc2626',   // rood
                'secondary' => '#16a34a', // groen
                'description' => 'Rood en groen voor Kerst',
            ],
            'oudjaar' => [
                'name' => 'Oudjaarsdag',
                'primary' => '#000000',   // zwart
                'secondary' => '#fbbf24', // goud
                'description' => 'Zwart en goud voor Oudjaarsdag',
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function getDefaultRanges(): array
    {
        return [
            ['days' => [5], 'theme' => 'sinterklaas'],
            ['days' => [24, 25, 26], 'theme' => 'kerst'],
            ['days' => [31], 'theme' => 'oudjaar'],
        ];
    }
}
