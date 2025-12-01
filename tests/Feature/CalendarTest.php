<?php

use App\Models\Calendar;
use App\Models\Invitation;
use App\Models\User;

test('authenticated user can view their calendars', function () {
    $user = User::factory()->create();

    $calendarData = [
        'owner_id' => $user->id,
        'title' => 'My Advent Calendar 2025',
        'year' => 2025,
        'description' => 'A special calendar',
        'theme_color' => '#ec4899',
    ];

    Calendar::query()->create($calendarData);
    Calendar::query()->create($calendarData);
    Calendar::query()->create($calendarData);

    $response = $this->actingAs($user)->get('/calendars');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Calendars/Index')
        ->has('calendars', 3)
    );
});

test('authenticated user can create a calendar', function () {
    $user = User::factory()->create();

    $calendarData = [
        'title' => 'My Advent Calendar 2025',
        'year' => 2025,
        'description' => 'A special calendar',
        'theme_color' => '#ec4899',
    ];

    $response = $this->actingAs($user)->post('/calendars', $calendarData);

    $response->assertRedirect();

    $this->assertDatabaseHas('calendars', [
        'owner_id' => $user->id,
        'title' => 'My Advent Calendar 2025',
        'year' => 2025,
    ]);

    // Should create 31 days
    $calendar = Calendar::where('owner_id', $user->id)->first();
    expect($calendar->days)->toHaveCount(31);
});

test('authenticated user can view their own calendar', function () {
    $user = User::factory()->create();
    $calendar = Calendar::query()->create([
        'owner_id' => $user->id,
        'title' => 'My Advent Calendar 2025',
        'year' => 2025,
        'description' => 'A special calendar',
        'theme_color' => '#ec4899',
    ]);

    $response = $this->actingAs($user)->get("/calendars/{$calendar->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Calendars/Show')
        ->has('calendar')
        ->where('calendar.id', $calendar->id)
    );
});

test('user cannot view another users calendar', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $calendar = Calendar::query()->create([
        'owner_id' => $user1->id,
        'title' => 'Other calendar',
        'year' => 2025,
        'description' => null,
        'theme_color' => '#ec4899',
    ]);

    $response = $this->actingAs($user2)->get("/calendars/{$calendar->id}");

    $response->assertForbidden();
});

test('authenticated user can delete their own calendar', function () {
    $user = User::factory()->create();
    $calendar = Calendar::query()->create([
        'owner_id' => $user->id,
        'title' => 'My calendar',
        'year' => 2025,
        'description' => null,
        'theme_color' => '#ec4899',
    ]);

    $response = $this->actingAs($user)->delete("/calendars/{$calendar->id}");

    $response->assertRedirect('/calendars');
    $this->assertDatabaseMissing('calendars', ['id' => $calendar->id]);
});

test('user cannot delete another users calendar', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $calendar = Calendar::query()->create([
        'owner_id' => $user1->id,
        'title' => 'Other calendar',
        'year' => 2025,
        'description' => null,
        'theme_color' => '#ec4899',
    ]);

    $response = $this->actingAs($user2)->delete("/calendars/{$calendar->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('calendars', ['id' => $calendar->id]);
});

test('calendar creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/calendars', [
        'title' => '',
        'year' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'year']);
});

test('calendar creation validates year range', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/calendars', [
        'title' => 'Test Calendar',
        'year' => 1999,
    ]);

    $response->assertSessionHasErrors('year');
});

test('guest cannot access calendars', function () {
    $response = $this->get('/calendars');

    $response->assertRedirect('/login');
});

test('calendar show includes invitation accept url when active invitation exists', function () {
    $owner = User::factory()->create();

    $calendar = Calendar::query()->create([
        'owner_id' => $owner->id,
        'title' => 'My Advent Calendar 2025',
        'year' => 2025,
        'description' => 'A special calendar',
        'theme_color' => '#ec4899',
    ]);

    $invitation = Invitation::query()->create([
        'email' => 'recipient@example.com',
        'token' => 'test-token-123',
        'calendar_id' => $calendar->id,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($owner)->get("/calendars/{$calendar->id}");

    $expectedUrl = route('invitations.accept', ['token' => $invitation->token], absolute: true);

    $response->assertInertia(fn ($page) => $page->component('Calendars/Show')
        ->where('invitationAcceptUrl', $expectedUrl)
    );
});
