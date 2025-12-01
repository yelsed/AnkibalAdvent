<?php

use App\Models\IntroPage;
use App\Models\User;

test('authenticated non-admin users are redirected to intro from home', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('intro'));
});

test('authenticated users can view the intro page', function (): void {
    $user = User::factory()->create();

    $intro = IntroPage::factory()->create([
        'title' => 'Test Intro',
        'body' => 'Test body',
    ]);

    $response = $this->actingAs($user)->get(route('intro'));

    $response->assertSuccessful();
    $response->assertInertia(function ($page) use ($intro): void {
        $page->component('Intro')
            ->where('introPage.id', $intro->id)
            ->where('introPage.title', $intro->title);
    });
});

test('guests cannot view the intro page', function (): void {
    $response = $this->get(route('intro'));

    $response->assertRedirect('/login');
});

test('admins can view the intro edit page', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $intro = IntroPage::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.intro.edit'));

    $response->assertSuccessful();
    $response->assertInertia(function ($page) use ($intro): void {
        $page->component('Admin/IntroPage')
            ->where('introPage.id', $intro->id);
    });
});

test('non-admin users cannot view the intro edit page', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.intro.edit'));

    $response->assertForbidden();
});

test('admins can update the intro page', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $intro = IntroPage::factory()->create([
        'title' => 'Old title',
        'body' => 'Old body',
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('admin.intro.update'), [
            'title' => 'New title',
            'body' => 'New body text',
        ]);

    $response->assertRedirect(route('admin.intro.edit'));

    $this->assertDatabaseHas('intro_pages', [
        'id' => $intro->id,
        'title' => 'New title',
        'body' => 'New body text',
    ]);
});
