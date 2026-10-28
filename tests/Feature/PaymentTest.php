<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Promo;
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

test('invoice menyertakan signed qr url', function () {
    $owner = User::factory()->create();
    $booking = makeBooking($owner, $this->court);

    $this->actingAs($owner)->get("/payments/{$booking->id}/invoice")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('Payments/Invoice')
            ->has('qrUrl')
            ->where('qrUrl', fn ($url) => str_contains((string) $url, 'signature=')));
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

test('pembayaran ditolak jika lock booking sudah kedaluwarsa', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'locked_until' => now()->subMinute(),
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/pay", ['method' => 'qris'])
        ->assertSessionHasErrors('error');

    expect($booking->fresh()->status)->toBe('expired');
});

test('bisa menggunakan kode promo persentase pada checkout', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $promo = Promo::create([
        'code' => 'TESTDISKON20',
        'title' => 'Diskon 20%',
        'discount_type' => 'percent',
        'discount_value' => 20,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'quota' => 10,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/apply-promo", ['code' => 'testdiskon20'])
        ->assertSessionHasNoErrors();

    $booking->refresh();
    expect($booking->promo_id)->toBe($promo->id);
    expect((float) $booking->discount_amount)->toBe(20000.0);
    expect((float) $booking->total_amount)->toBe(80000.0);
});

test('bisa menggunakan kode promo nominal tetap pada checkout', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 100000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $promo = Promo::create([
        'code' => 'TESTHEMAT25',
        'title' => 'Potongan 25rb',
        'discount_type' => 'fixed',
        'discount_value' => 25000,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'quota' => 10,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/apply-promo", ['code' => 'TESTHEMAT25'])
        ->assertSessionHasNoErrors();

    $booking->refresh();
    expect($booking->promo_id)->toBe($promo->id);
    expect((float) $booking->discount_amount)->toBe(25000.0);
    expect((float) $booking->total_amount)->toBe(75000.0);
});

test('kode promo tidak valid ditolak', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/apply-promo", ['code' => 'KODEASAL'])
        ->assertSessionHasErrors('promo_code');
});

test('bisa menghapus kode promo yang telah dipasang', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $promo = Promo::create([
        'code' => 'TESTHAPUS',
        'title' => 'Test Hapus',
        'discount_type' => 'percent',
        'discount_value' => 50,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'is_active' => true,
    ]);

    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'promo_id' => $promo->id,
        'discount_amount' => 50000,
        'total_amount' => 50000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)->delete("/bookings/{$booking->id}/remove-promo")
        ->assertSessionHasNoErrors();

    $booking->refresh();
    expect($booking->promo_id)->toBeNull();
    expect((float) $booking->discount_amount)->toBe(0.0);
    expect((float) $booking->total_amount)->toBe(100000.0);
});

test('pembayaran lunas meng-increment used_count promo', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $promo = Promo::create([
        'code' => 'TESTUSED',
        'title' => 'Test Used Count',
        'discount_type' => 'fixed',
        'discount_value' => 20000,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'quota' => 10,
        'used_count' => 2,
        'is_active' => true,
    ]);

    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'promo_id' => $promo->id,
        'discount_amount' => 20000,
        'total_amount' => 80000,
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/pay", ['method' => 'qris'])
        ->assertRedirect('/bookings');

    expect($booking->fresh()->payment_status)->toBe('paid');
    expect($promo->fresh()->used_count)->toBe(3);
});

test('webhook midtrans dengan fraud status challenge tidak menandai lunas', function () {
    config(['midtrans.server_key' => 'test-key']);

    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update(['status' => 'pending', 'payment_status' => 'pending']);

    Payment::create([
        'booking_id' => $booking->id,
        'amount' => 100000,
        'method' => 'credit_card',
        'provider' => 'midtrans',
        'status' => 'pending',
        'transaction_reference' => 'MID-CHALLENGE',
    ]);

    $signature = hash('sha512', 'MID-CHALLENGE'.'200'.'100000'.'test-key');

    $this->postJson('/payments/notification', [
        'order_id' => 'MID-CHALLENGE',
        'status_code' => '200',
        'gross_amount' => '100000',
        'transaction_status' => 'capture',
        'fraud_status' => 'challenge',
        'signature_key' => $signature,
    ])->assertOk();

    expect(Payment::where('transaction_reference', 'MID-CHALLENGE')->first()->status)->toBe('pending');
    expect($booking->fresh()->payment_status)->toBe('pending');
});

test('webhook midtrans status expire mengupdate status booking menjadi expired', function () {
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
        'transaction_reference' => 'MID-EXPIRE',
    ]);

    $signature = hash('sha512', 'MID-EXPIRE'.'200'.'100000'.'test-key');

    $this->postJson('/payments/notification', [
        'order_id' => 'MID-EXPIRE',
        'status_code' => '200',
        'gross_amount' => '100000',
        'transaction_status' => 'expire',
        'signature_key' => $signature,
    ])->assertOk();

    expect(Payment::where('transaction_reference', 'MID-EXPIRE')->first()->status)->toBe('failed');
    expect($booking->fresh()->status)->toBe('expired');
});

test('booking dengan tagihan 0 dari promo 100 persen langsung diselesaikan tanpa error snap', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 0,
        'locked_until' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/pay", ['method' => 'qris'])
        ->assertRedirect('/bookings');

    expect($booking->fresh()->payment_status)->toBe('paid');
    expect($booking->fresh()->status)->toBe('confirmed');
});

test('pembayaran berulang mengupdate record pending alih-alih membuat duplikat', function () {
    $user = User::factory()->create();
    $booking = makeBooking($user, $this->court);
    $booking->update([
        'status' => 'pending',
        'payment_status' => 'pending',
        'locked_until' => now()->addMinutes(5),
    ]);

    $existingPayment = Payment::create([
        'booking_id' => $booking->id,
        'amount' => 100000,
        'method' => 'bank_transfer',
        'provider' => 'midtrans',
        'status' => 'pending',
        'transaction_reference' => 'MID-EXISTING',
    ]);

    $this->actingAs($user)->post("/bookings/{$booking->id}/pay", ['method' => 'qris'])
        ->assertRedirect('/bookings');

    expect(Payment::where('booking_id', $booking->id)->count())->toBe(1);
    expect($existingPayment->fresh()->method)->toBe('qris');
    expect($existingPayment->fresh()->status)->toBe('paid');
});

test('webhook midtrans menolak payload yang tidak lengkap dengan 400 Bad Request alih-alih error 500', function () {
    config(['midtrans.server_key' => 'test-key']);

    // Missing status_code and gross_amount
    $this->postJson('/payments/notification', [
        'order_id' => 'MID-INCOMPLETE',
        'signature_key' => 'dummy-signature',
    ])->assertStatus(400);

    // Empty payload
    $this->postJson('/payments/notification', [])
        ->assertStatus(400);
});
