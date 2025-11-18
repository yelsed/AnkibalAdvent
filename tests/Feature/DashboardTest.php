<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('regular users cannot access the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertForbidden();
});

test('admin users can visit the dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
});
