<?php

use App\Models\Calendar;
use App\Models\CalendarDay;
use App\Models\User;

test('user can unlock a calendar day and see confetti', function () {
    // Create user and calendar with days
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);

    // Create day that can be unlocked
    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'gift_type' => 'text',
        'title' => 'Welcome Gift',
        'content_text' => 'Enjoy your first surprise!',
        'unlocked_at' => null,
    ]);

    // Mock December 1st so day 1 can be unlocked
    $this->travelTo(now()->setMonth(12)->setDay(1));

    $this->actingAs($user);

    $page = visit("/calendars/{$calendar->id}");

    $page->assertSee($calendar->title)
        ->assertSee('1')
        ->assertNoJavascriptErrors()
        ->click('button:has-text("1")')
        ->waitFor('[data-vaul-drawer]', 2)
        ->assertSee('Day 1')
        ->assertSee('Welcome Gift')
        ->assertSee('Enjoy your first surprise!')
        ->screenshot('day-unlocked');
})->skip('Browser tests require setup');

test('locked days show gift wrap design', function () {
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);

    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 25,
        'unlocked_at' => null,
    ]);

    // Mock December 1st so day 25 is locked
    $this->travelTo(now()->setMonth(12)->setDay(1));

    $this->actingAs($user);

    $page = visit("/calendars/{$calendar->id}");

    $page->assertSee('25')
        ->assertNoJavascriptErrors()
        ->screenshot('gift-wrap-design');
})->skip('Browser tests require setup');

test('admin can manage calendar days', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create();
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
    ]);

    $this->actingAs($admin);

    $page = visit("/admin/calendars/{$calendar->id}/manage");

    $page->assertSee('Manage Calendar Days')
        ->assertSee('Select Day')
        ->click('button:has-text("1")')
        ->fill('[name="title"]', 'Updated Gift Title')
        ->fill('[name="content_text"]', 'This is an updated gift message!')
        ->click('button[type="submit"]')
        ->assertSee('Day updated successfully!')
        ->assertNoJavascriptErrors();
})->skip('Browser tests require setup');


