<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin dapat mengakses admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard'));
});

test('player tidak dapat mengakses admin dashboard', function () {
    $player = User::factory()->create(['role' => 'player']);

    $this->actingAs($player)->get('/admin')->assertForbidden();
});

test('user yang di-suspend tidak bisa login', function () {
    $user = User::factory()->create(['email' => 'suspend@example.com', 'password' => 'secret123', 'is_active' => false]);

    $this->post('/login', ['email' => 'suspend@example.com', 'password' => 'secret123'])
        ->assertSessionHasErrors('email');
});
