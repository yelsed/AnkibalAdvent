<?php

use App\Models\Calendar;
use App\Models\CalendarDay;
use App\Models\User;

test('user can export calendar to PDF', function () {
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['owner_id' => $user->id]);

    // Create some days with content
    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'gift_type' => 'text',
        'content_text' => 'Test content for day 1',
        'title' => 'Day 1 Title',
    ]);

    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 2,
        'gift_type' => 'image_text',
        'content_text' => 'Test content for day 2',
        'content_image_path' => 'calendar_images/test.jpg',
        'title' => 'Day 2 Title',
    ]);

    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 3,
        'gift_type' => 'product',
        'product_code' => 'PROD123',
        'content_text' => 'Product description',
    ]);

    // Create a day without content (should not be in PDF)
    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 4,
        'gift_type' => 'text',
        'content_text' => __('calendar.gift_hasnt_setup'),
    ]);

    $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/export-pdf");

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

test('recipient can export calendar to PDF', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $calendar = Calendar::factory()->create([
        'owner_id' => $owner->id,
        'recipient_id' => $recipient->id,
    ]);

    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'gift_type' => 'text',
        'content_text' => 'Test content',
    ]);

    $response = $this->actingAs($recipient)->get("/calendars/{$calendar->id}/export-pdf");

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('admin can export any calendar to PDF', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['owner_id' => $user->id]);

    CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'gift_type' => 'text',
        'content_text' => 'Test content',
    ]);

    $response = $this->actingAs($admin)->get("/calendars/{$calendar->id}/export-pdf");

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('unauthorized user cannot export calendar to PDF', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $calendar = Calendar::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($otherUser)->get("/calendars/{$calendar->id}/export-pdf");

    $response->assertForbidden();
});

test('unauthenticated user cannot export calendar to PDF', function () {
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['owner_id' => $user->id]);

    $response = $this->get("/calendars/{$calendar->id}/export-pdf");

    $response->assertRedirect(route('login'));
});
