<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'venue_owner']);

    $venue = Venue::create([
        'name' => 'Venue Test',
        'slug' => 'venue-test-'.Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
        'owner_id' => $this->owner->id,
    ]);

    $this->court = Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court 1',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);
});

function makePaidBooking(User $user, Court $court): Booking
{
    return Booking::create([
        'user_id' => $user->id,
        'court_id' => $court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);
}

test('halaman scan butuh login', function () {
    $booking = makePaidBooking(User::factory()->create(), $this->court);

    $this->get("/checkin/{$booking->id}/scan")->assertRedirect('/login');
});

test('player tidak dapat mengakses halaman scan', function () {
    $player = User::factory()->create(['role' => 'player']);
    $booking = makePaidBooking($player, $this->court);

    $this->actingAs($player)->get("/checkin/{$booking->id}/scan")->assertForbidden();
});

test('halaman scan menampilkan booking yang sudah dibayar untuk owner venue', function () {
    $booking = makePaidBooking(User::factory()->create(), $this->court);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkin/Scan')
            ->where('booking.status', 'confirmed'));
});

test('owner venue lain tidak dapat scan booking venue orang lain', function () {
    $otherOwner = User::factory()->create(['role' => 'venue_owner']);
    $otherVenue = Venue::create([
        'name' => 'Venue Lain',
        'slug' => 'venue-lain-'.Str::random(6),
        'address' => 'Jl. Lain',
        'city' => 'Bandung',
        'status' => 'approved',
        'owner_id' => $otherOwner->id,
    ]);
    $otherCourt = Court::create([
        'venue_id' => $otherVenue->id,
        'name' => 'Court A',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);
    $booking = makePaidBooking(User::factory()->create(), $otherCourt);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan")->assertForbidden();
});

test('halaman scan menolak booking belum dibayar', function () {
    $user = User::factory()->create();
    $booking = Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
    ]);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan")->assertForbidden();
});

test('check-in dari hasil scan mengubah status booking', function () {
    $booking = makePaidBooking(User::factory()->create(), $this->court);

    $this->actingAs($this->owner)->patch("/checkin/{$booking->id}", ['status' => 'checked_in'])
        ->assertRedirect();

    expect($booking->fresh()->status)->toBe('checked_in');
});

test('daftar check-in hanya menampilkan booking venue milik owner', function () {
    $otherOwner = User::factory()->create(['role' => 'venue_owner']);
    $otherVenue = Venue::create([
        'name' => 'Venue Lain',
        'slug' => 'venue-lain-'.Str::random(6),
        'address' => 'Jl. Lain',
        'city' => 'Bandung',
        'status' => 'approved',
        'owner_id' => $otherOwner->id,
    ]);
    $otherCourt = Court::create([
        'venue_id' => $otherVenue->id,
        'name' => 'Court A',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    $mine = makePaidBooking(User::factory()->create(), $this->court);
    $theirs = makePaidBooking(User::factory()->create(), $otherCourt);

    $mine->update(['booking_date' => now()->toDateString(), 'start_time' => '08:00', 'end_time' => '09:00']);
    $theirs->update(['booking_date' => now()->toDateString()]);

    $this->actingAs($this->owner)->get('/checkin')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkin/Index')
            ->has('bookings', 1)
            ->where('bookings.0.id', $mine->id));
});

test('check-in dan scan menolak booking yang sudah dibatalkan atau kedaluwarsa', function () {
    $booking = makePaidBooking(User::factory()->create(), $this->court);
    $booking->update(['status' => 'cancelled']);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan")
        ->assertStatus(422);

    $this->actingAs($this->owner)->patch("/checkin/{$booking->id}", ['status' => 'checked_in'])
        ->assertStatus(422);

    $booking->update(['status' => 'expired']);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan")
        ->assertStatus(422);

    $this->actingAs($this->owner)->patch("/checkin/{$booking->id}", ['status' => 'checked_in'])
        ->assertStatus(422);
});

test('scan dengan signature yang dimanipulasi akan ditolak', function () {
    $booking = makePaidBooking(User::factory()->create(), $this->court);

    $this->actingAs($this->owner)->get("/checkin/{$booking->id}/scan?signature=invalidsignature")
        ->assertForbidden();
});
