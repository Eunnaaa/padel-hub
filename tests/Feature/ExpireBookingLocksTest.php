<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;

beforeEach(function () {
    $venue = Venue::create([
        'name' => 'Venue Test Expire',
        'slug' => 'venue-test-expire-'.Str::random(6),
        'address' => 'Jl. Test Expire',
        'city' => 'Jakarta',
        'status' => 'approved',
    ]);

    $this->court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court Expire',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    $this->user = User::factory()->create(['role' => 'player']);
});

test('command bookings:expire-locks mengkedaluwarsa booking pending yang locked_until telah lewat', function () {
    $expiredBooking = Booking::create([
        'court_id' => $this->court->id,
        'user_id' => $this->user->id,
        'booking_date' => now()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'total_amount' => 100000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'locked_until' => now()->subMinutes(6),
    ]);

    $activeBooking = Booking::create([
        'court_id' => $this->court->id,
        'user_id' => $this->user->id,
        'booking_date' => now()->toDateString(),
        'start_time' => '14:00',
        'end_time' => '15:00',
        'total_amount' => 100000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'locked_until' => now()->addMinutes(3),
    ]);

    $paidBooking = Booking::create([
        'court_id' => $this->court->id,
        'user_id' => $this->user->id,
        'booking_date' => now()->toDateString(),
        'start_time' => '16:00',
        'end_time' => '17:00',
        'total_amount' => 100000,
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'locked_until' => now()->subMinutes(10),
    ]);

    $this->artisan('bookings:expire-locks')
        ->expectsOutput('Expired 1 pending booking lock(s).')
        ->assertSuccessful();

    expect($expiredBooking->fresh()->status)->toBe('expired');
    expect($activeBooking->fresh()->status)->toBe('pending');
    expect($paidBooking->fresh()->status)->toBe('confirmed');
});
