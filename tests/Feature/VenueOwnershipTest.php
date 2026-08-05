<?php

use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

function makeVenue(User $owner): Venue
{
    return Venue::create([
        'name' => 'Venue Test',
        'slug' => 'venue-test-'.Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
        'owner_id' => $owner->id,
    ]);
}

test('player tidak dapat mengakses halaman venue', function () {
    $player = User::factory()->create(['role' => 'player']);

    $this->actingAs($player)->get('/venues')->assertForbidden();
});

test('venue owner hanya melihat venue miliknya', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);
    $mine = makeVenue($owner);
    makeVenue(User::factory()->create(['role' => 'venue_owner']));

    $this->actingAs($owner)->get('/venues')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Venues/Index')
            ->where('venues.0.id', $mine->id));
});

test('venue owner hanya bisa edit venue miliknya', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);
    $mine = makeVenue($owner);
    $theirs = makeVenue(User::factory()->create(['role' => 'venue_owner']));

    $this->actingAs($owner)->get("/venues/{$theirs->id}/edit")->assertForbidden();
    $this->actingAs($owner)->get("/venues/{$mine->id}/edit")->assertOk();
});

test('venue owner hanya bisa buat court di venue miliknya', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);
    $mine = makeVenue($owner);
    $theirs = makeVenue(User::factory()->create(['role' => 'venue_owner']));

    $this->actingAs($owner)->post('/courts', [
        'venue_id' => $theirs->id,
        'name' => 'Court X',
        'price_per_hour' => 100000,
        'status' => 'available',
    ])->assertForbidden();

    $this->actingAs($owner)->post('/courts', [
        'venue_id' => $mine->id,
        'name' => 'Court 1',
        'price_per_hour' => 100000,
        'status' => 'available',
    ])->assertRedirect('/courts');
});

test('player tidak dapat mengakses venue dashboard', function () {
    $player = User::factory()->create(['role' => 'player']);

    $this->actingAs($player)->get('/venue-dashboard')->assertForbidden();
});

test('admin dapat mengakses venue milik siapa pun', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $venue = makeVenue(User::factory()->create(['role' => 'venue_owner']));

    $this->actingAs($admin)->get("/venues/{$venue->id}/edit")->assertOk();
});
