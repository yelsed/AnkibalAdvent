<?php

use App\Models\Calendar;
use App\Models\User;

test('admin can access calendar management page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $calendar = Calendar::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/calendars/{$calendar->id}/manage");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/CalendarDays')
        ->has('calendar')
        ->where('calendar.id', $calendar->id)
    );
});

test('non-admin cannot access calendar management page', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $calendar = Calendar::factory()->create();

    $response = $this->actingAs($user)->get("/admin/calendars/{$calendar->id}/manage");

    $response->assertForbidden();
});

test('guest cannot access calendar management page', function () {
    $calendar = Calendar::factory()->create();

    $response = $this->get("/admin/calendars/{$calendar->id}/manage");

    $response->assertRedirect('/login');
});


