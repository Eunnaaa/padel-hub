<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

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

function makeBooking(User $user, Court $court): Booking
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

test('invoice hanya bisa diakses pemilik booking', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $booking = makeBooking($owner, $this->court);

    $this->actingAs($other)->get("/payments/{$booking->id}/invoice")->assertForbidden();
    $this->actingAs($owner)->get("/payments/{$booking->id}/invoice")->assertOk();
});

test('riwayat pembayaran hanya menampilkan transaksi milik user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Payment::create([
        'booking_id' => makeBooking($owner, $this->court)->id,
        'amount' => 100000,
        'method' => 'qris',
        'status' => 'paid',
    ]);
    Payment::create([
        'booking_id' => makeBooking($other, $this->court)->id,
        'amount' => 100000,
        'method' => 'bank_transfer',
        'status' => 'paid',
    ]);

    $this->actingAs($owner)->get('/payments')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Payments/Index')
            ->has('payments', 1));
});

test('pembayaran mengirim notifikasi', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update(['status' => 'pending', 'payment_status' => 'pending']);

    $this->actingAs($user)->post("/bookings/{$booking->id}/pay", ['method' => 'qris'])
        ->assertRedirect('/bookings');

    expect($user->notifications()->count())->toBe(1);
    expect($booking->fresh()->payment_status)->toBe('paid');
});

test('webhook midtrans menolak signature tidak valid', function () {
    config(['midtrans.server_key' => 'test-key']);

    $this->postJson('/payments/notification', [
        'order_id' => 'MID-XXX',
        'status_code' => '200',
        'gross_amount' => '100000',
        'transaction_status' => 'settlement',
        'signature_key' => 'wrong-signature',
    ])->assertForbidden();
});

test('webhook midtrans dengan signature valid meng-update pembayaran', function () {
    config(['midtrans.server_key' => 'test-key']);

    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update(['status' => 'pending', 'payment_status' => 'pending']);

    Payment::create([
        'booking_id' => $booking->id,
        'amount' => 100000,
        'method' => 'bank_transfer',
        'provider' => 'midtrans',
        'status' => 'pending',
        'transaction_reference' => 'MID-ABC123',
    ]);

    $signature = hash('sha512', 'MID-ABC123'.'200'.'100000'.'test-key');

    $this->postJson('/payments/notification', [
        'order_id' => 'MID-ABC123',
        'status_code' => '200',
        'gross_amount' => '100000',
        'transaction_status' => 'settlement',
        'signature_key' => $signature,
    ])->assertOk();

    expect(Payment::where('transaction_reference', 'MID-ABC123')->first()->status)->toBe('paid');
    expect($booking->fresh()->payment_status)->toBe('paid');
    expect($booking->fresh()->status)->toBe('confirmed');
});
