<?php

use App\Models\Booking;
use App\Models\Court;
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

test('venue owner baru membuat venue dengan status pending dan tidak bisa self approve', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);

    $this->actingAs($owner)->post('/venues', [
        'name' => 'Venue Baru',
        'slug' => 'venue-baru-'.Str::random(6),
        'address' => 'Jl. Baru No. 1',
        'city' => 'Jakarta',
        'status' => 'approved', // mencoba memanipulasi status
    ])->assertRedirect('/venues');

    $venue = Venue::where('owner_id', $owner->id)->latest('id')->first();
    expect($venue)->not->toBeNull();
    expect($venue->status)->toBe('pending');

    // Coba manipulasi status lewat update
    $this->actingAs($owner)->put("/venues/{$venue->id}", [
        'name' => 'Venue Baru Diupdate',
        'slug' => $venue->slug,
        'address' => 'Jl. Baru No. 1',
        'city' => 'Jakarta',
        'status' => 'approved',
    ])->assertRedirect('/venues');

    expect($venue->fresh()->status)->toBe('pending');
});

test('lapangan tidak dapat dihapus jika memiliki booking aktif di masa depan', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);
    $venue = makeVenue($owner);
    $court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court Deletion Test',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    Booking::create([
        'user_id' => User::factory()->create()->id,
        'court_id' => $court->id,
        'booking_date' => now()->addDays(2)->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this->actingAs($owner)->delete("/courts/{$court->id}")
        ->assertStatus(422);

    expect(Court::find($court->id))->not->toBeNull();
});

test('venue tidak dapat dihapus jika lapangannya memiliki booking aktif', function () {
    $owner = User::factory()->create(['role' => 'venue_owner']);
    $venue = makeVenue($owner);
    $court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court Venue Test',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    Booking::create([
        'user_id' => User::factory()->create()->id,
        'court_id' => $court->id,
        'booking_date' => now()->addDays(2)->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this->actingAs($owner)->delete("/venues/{$venue->id}")
        ->assertStatus(422);

    expect(Venue::find($venue->id))->not->toBeNull();
});
