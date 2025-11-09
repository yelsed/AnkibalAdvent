<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();

        foreach ($users as $user) {
            $calendar = \App\Models\Calendar::factory()->create([
                'user_id' => $user->id,
                'year' => now()->year,
            ]);

            // Create all 31 days for the calendar
            for ($day = 1; $day <= 31; $day++) {
                \App\Models\CalendarDay::factory()->create([
                    'calendar_id' => $calendar->id,
                    'day_number' => $day,
                ]);
            }
        }
    }
}
