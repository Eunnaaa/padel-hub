<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;

beforeEach(function () {
    $venue = Venue::create([
        'name' => 'Venue Test',
        'slug' => 'venue-test-'.Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
    ]);

    $this->court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court 1',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);
});

test('recurring booking membuat seri 4 minggu', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'recurrence_rule' => 'weekly',
    ])->assertRedirect('/bookings');

    $bookings = Booking::where('user_id', $user->id)->get();
    expect($bookings)->toHaveCount(4);
    expect($bookings->pluck('booking_date')->unique())->toHaveCount(4);
    expect($bookings->pluck('recurring_group_id')->unique())->toHaveCount(1);
});

test('booking overlap ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:30',
        'end_time' => '10:30',
    ])->assertSessionHasErrors('start_time');
});

test('booking pending dengan lock aktif memblokir slot', function () {
    $user = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:30',
        'end_time' => '10:30',
    ])->assertSessionHasErrors('start_time');
});

test('booking pending dengan lock kedaluwarsa tidak memblokir slot', function () {
    $user = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->subMinute(),
    ]);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:30',
        'end_time' => '10:30',
    ])->assertSessionHasNoErrors()->assertRedirect('/bookings');
});

test('booking baru mendapat lock 5 menit', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
    ])->assertRedirect('/bookings');

    $booking = Booking::where('user_id', $user->id)->first();
    expect($booking->status)->toBe('pending');
    expect($booking->locked_until)->not->toBeNull();
    expect($booking->locked_until->isFuture())->toBeTrue();
});

test('command expire-locks mengubah pending tanpa lock aktif jadi expired', function () {
    $user = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->subMinute(),
    ]);

    $this->artisan('bookings:expire-locks');

    expect(Booking::where('user_id', $user->id)->first()->status)->toBe('expired');
});

test('command expire-locks tidak menyentuh booking dengan lock masih aktif', function () {
    $user = User::factory()->create(['role' => 'player']);

    Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->artisan('bookings:expire-locks');

    expect(Booking::where('user_id', $user->id)->first()->status)->toBe('pending');
});

test('cancel seri membatalkan semua booking dalam grup', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'recurrence_rule' => 'weekly',
    ]);

    $first = Booking::where('user_id', $user->id)->first();

    $this->actingAs($user)->delete("/bookings/{$first->id}", ['cancel_series' => true])
        ->assertRedirect('/bookings');

    expect(Booking::where('user_id', $user->id)->whereNotIn('status', ['cancelled'])->count())->toBe(0);
});
