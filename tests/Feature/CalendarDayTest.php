<?php

use App\Models\Calendar;
use App\Models\CalendarDay;
use App\Models\User;

test('user can unlock a day in December when day number is reached', function () {
    // Mock December 10th at 07:00
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(7, 0));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'day']);

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('user cannot unlock a future day', function () {
    // Mock December 5th at 07:00
    $this->travelTo(now()->setMonth(12)->setDay(5)->setTime(7, 0));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertForbidden();

    $day->refresh();
    expect($day->unlocked_at)->toBeNull();
});

test('user cannot unlock days outside of December', function () {
    // Mock November 30th
    $this->travelTo(now()->setMonth(11)->setDay(30));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertForbidden();

    $day->refresh();
    expect($day->unlocked_at)->toBeNull();
});

test('unlocking an already unlocked day returns the day data', function () {
    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->unlocked()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'This day has already been unlocked.',
    ]);
});

test('user cannot unlock days from another users calendar', function () {
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(7, 0));

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user1->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user2)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertForbidden();
});

test('user cannot unlock a day before 07:00 on that day', function () {
    // Mock December 10th at 06:59
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(6, 59));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertForbidden();

    $day->refresh();
    expect($day->unlocked_at)->toBeNull();
});

test('user can unlock a day at 07:00 on that day', function () {
    // Mock December 10th at 07:00
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(7, 0));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'day']);

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('user can unlock a day after 07:00 on that day', function () {
    // Mock December 10th at 10:30
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(10, 30));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 10,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('user can unlock previous days after 07:00', function () {
    // Mock December 10th at 08:00 - can unlock day 9
    $this->travelTo(now()->setMonth(12)->setDay(10)->setTime(8, 0));

    $user = User::factory()->create();
    $calendar = Calendar::factory()->create(['user_id' => $user->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 9,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('admin can update calendar day content', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create();
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
    ]);

    $response = $this->actingAs($admin)->put("/calendar-days/{$day->id}", [
        'gift_type' => 'text',
        'title' => 'A Special Gift',
        'content_text' => 'This is a special message for you!',
    ]);

    $response->assertRedirect();

    $day->refresh();
    expect($day->title)->toBe('A Special Gift');
    expect($day->content_text)->toBe('This is a special message for you!');
});

test('non-admin cannot update calendar day content', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $calendar = Calendar::factory()->create();
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
    ]);

    $response = $this->actingAs($user)->put("/calendar-days/{$day->id}", [
        'gift_type' => 'text',
        'title' => 'Hacked',
        'content_text' => 'Trying to hack',
    ]);

    $response->assertForbidden();
});

test('calendar day update validates gift type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $day = CalendarDay::factory()->create();

    $response = $this->actingAs($admin)->put("/calendar-days/{$day->id}", [
        'gift_type' => 'invalid_type',
        'content_text' => 'Some text',
    ]);

    $response->assertSessionHasErrors('gift_type');
});

test('calendar day update requires image for image_text type', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $day = CalendarDay::factory()->create();

    $response = $this->actingAs($admin)->put("/calendar-days/{$day->id}", [
        'gift_type' => 'image_text',
        'content_text' => 'Some text',
    ]);

    $response->assertSessionHasErrors('content_image');
});

test('admin can unlock any day regardless of date restrictions', function () {
    // Mock December 5th
    $this->travelTo(now()->setMonth(12)->setDay(5));

    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create(['user_id' => $admin->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 31, // Future day
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($admin)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();
    $response->assertJsonStructure(['message', 'day']);

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('admin can unlock days outside of December', function () {
    // Mock November 30th
    $this->travelTo(now()->setMonth(11)->setDay(30));

    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create(['user_id' => $admin->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 1,
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($admin)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});

test('admin can unlock days from calendars they do not own', function () {
    $this->travelTo(now()->setMonth(12)->setDay(5));

    $owner = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create(['user_id' => $owner->id]);
    $day = CalendarDay::factory()->create([
        'calendar_id' => $calendar->id,
        'day_number' => 31, // Future day
        'unlocked_at' => null,
    ]);

    $response = $this->actingAs($admin)->postJson("/calendar-days/{$day->id}/unlock");

    $response->assertSuccessful();

    $day->refresh();
    expect($day->unlocked_at)->not->toBeNull();
});
