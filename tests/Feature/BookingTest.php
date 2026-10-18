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

    $res = $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:30',
        'end_time' => '10:30',
    ]);
    $created = Booking::where('user_id', $user->id)->latest('id')->first();
    $res->assertSessionHasNoErrors()->assertRedirect("/bookings/{$created->id}/pay");
});

test('booking baru mendapat lock 5 menit', function () {
    $user = User::factory()->create(['role' => 'player']);

    $res = $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    $booking = Booking::where('user_id', $user->id)->first();
    $res->assertRedirect("/bookings/{$booking->id}/pay");
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

test('booking lapangan maintenance atau inaktif ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);
    $this->court->update(['status' => 'maintenance']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
    ])->assertStatus(422);
});

test('booking pada venue yang belum diapprove ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);
    $this->court->venue->update(['status' => 'pending']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
    ])->assertStatus(422);
});

test('update booking tidak bisa memanipulasi payment_status', function () {
    $user = User::factory()->create(['role' => 'player']);
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

    $this->actingAs($user)->put("/bookings/{$booking->id}", [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '14:00',
        'end_time' => '15:00',
        'payment_status' => 'paid',
        'status' => 'confirmed',
    ])->assertRedirect('/bookings');

    $booking->refresh();
    expect($booking->payment_status)->toBe('pending');
    expect($booking->status)->toBe('pending');
    expect(substr($booking->start_time, 0, 5))->toBe('14:00');
});

test('booking yang sudah dibayar tidak bisa diupdate jadwalnya sembarangan', function () {
    $user = User::factory()->create(['role' => 'player']);
    $booking = Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this->actingAs($user)->put("/bookings/{$booking->id}", [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '14:00',
        'end_time' => '15:00',
    ])->assertStatus(422);
});

test('booking yang sudah dibayar atau check in tidak bisa dibatalkan langsung oleh user', function () {
    $user = User::factory()->create(['role' => 'player']);
    $booking = Booking::create([
        'user_id' => $user->id,
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this->actingAs($user)->delete("/bookings/{$booking->id}")
        ->assertStatus(422);

    expect($booking->fresh()->status)->toBe('confirmed');

    $booking->update(['status' => 'checked_in']);
    $this->actingAs($user)->delete("/bookings/{$booking->id}")
        ->assertStatus(422);

    expect($booking->fresh()->status)->toBe('checked_in');
});

test('booking dengan durasi kurang dari 1 jam ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '09:30',
    ])->assertSessionHasErrors('end_time');
});

test('booking dengan durasi lebih dari 4 jam ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '13:30',
    ])->assertSessionHasErrors('end_time');
});

test('booking dengan interval selain kelipatan 30 menit ditolak', function () {
    $user = User::factory()->create(['role' => 'player']);

    $this->actingAs($user)->post('/bookings', [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '09:15',
        'end_time' => '10:15',
    ])->assertSessionHasErrors('start_time');
});

test('edit dan update booking pending yang lock-nya kedaluwarsa ditolak 422', function () {
    $user = User::factory()->create(['role' => 'player']);
    $booking = Booking::create([
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

    $this->actingAs($user)->get("/bookings/{$booking->id}/edit")
        ->assertStatus(422);

    $this->actingAs($user)->put("/bookings/{$booking->id}", [
        'court_id' => $this->court->id,
        'booking_date' => now()->addWeek()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ])->assertStatus(422);

    expect($booking->fresh()->status)->toBe('expired');
});
