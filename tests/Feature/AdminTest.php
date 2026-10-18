<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;
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

test('user yang aktif lalu di-suspend langsung ter-logout pada request berikutnya', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)->get('/dashboard')->assertOk();

    $user->update(['is_active' => false]);

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('admin diarahkan ke admin dashboard saat membuka /dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('admin dapat mengubah status operasional lapangan', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $venue = Venue::create([
        'name' => 'Venue Test',
        'slug' => 'venue-test-'.Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
    ]);
    $court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court 1',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.courts.toggle-status', $court->id))
        ->assertRedirect();

    expect($court->fresh()->status)->toBe('maintenance');
});
