<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'venue_owner']);

    $this->venue = Venue::create([
        'name' => 'Venue Dashboard Test',
        'slug' => 'venue-dash-'.Str::random(6),
        'address' => 'Jl. Padel No. 1',
        'city' => 'Jakarta',
        'status' => 'approved',
        'owner_id' => $this->owner->id,
    ]);

    $this->court = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Court Dash 1',
        'price_per_hour' => 150000,
        'status' => 'available',
    ]);
});

test('venue owner dapat mengakses venue dashboard', function () {
    $this->actingAs($this->owner)->get('/venue-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('VenueDashboard/Index')
            ->has('revenue')
            ->has('bookings')
            ->has('trendDays')
            ->has('peakHours')
            ->has('courtUsage'));
});

test('player tidak dapat mengakses venue dashboard', function () {
    $player = User::factory()->create(['role' => 'player']);

    $this->actingAs($player)->get('/venue-dashboard')->assertForbidden();
});

test('venue dashboard mengagregasi data pendapatan dan okupansi dengan benar', function () {
    $player = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $player->id,
        'court_id' => $this->court->id,
        'booking_date' => today()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 150000,
    ]);

    $this->actingAs($this->owner)->get('/venue-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('VenueDashboard/Index')
            ->where('revenue.today', 150000)
            ->where('bookings.today', 1));
});

test('venue owner baru tanpa venue tetap dapat mengakses dashboard dengan aman', function () {
    $newOwner = User::factory()->create(['role' => 'venue_owner']);

    $this->actingAs($newOwner)->get('/venue-dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('VenueDashboard/Index')
            ->where('revenue.today', 0)
            ->where('bookings.today', 0)
            ->has('trendDays')
            ->has('peakHours')
            ->has('courtUsage'));
});
