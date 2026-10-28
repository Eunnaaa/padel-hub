<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile fields profil lengkap dapat diperbarui', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'phone' => '+62 812 3456 7890',
            'gender' => 'male',
            'birth_date' => '1995-06-15',
            'skill_level' => 'intermediate',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->phone)->toBe('+62 812 3456 7890')
        ->and($user->gender)->toBe('male')
        ->and($user->birth_date->toDateString())->toBe('1995-06-15')
        ->and($user->skill_level)->toBe('intermediate');
});

test('birth date tidak boleh di masa depan', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'birth_date' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('birth_date');
});

test('avatar dapat diunggah', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();

    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});

test('file non-gambar ditolak sebagai avatar', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('dokumen.txt', 100),
        ])->assertSessionHasErrors('avatar');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertTrue($user->fresh()->trashed());
    $this->assertNull(User::find($user->id));
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('user tidak dapat menghapus akun jika memiliki booking aktif', function () {
    $user = User::factory()->create();
    $venue = \App\Models\Venue::create([
        'name' => 'Venue Test Profile',
        'slug' => 'venue-test-profile-'.\Illuminate\Support\Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
    ]);
    $court = \App\Models\Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court P1',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    \App\Models\Booking::create([
        'user_id' => $user->id,
        'court_id' => $court->id,
        'booking_date' => now()->addDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'confirmed',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    expect($user->fresh())->not->toBeNull();
});

test('user yang dihapus berstatus soft-deleted dan tidak menghapus booking lampau', function () {
    $user = User::factory()->create();
    $venue = \App\Models\Venue::create([
        'name' => 'Venue Test Profile 2',
        'slug' => 'venue-test-profile-2-'.\Illuminate\Support\Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
    ]);
    $court = \App\Models\Court::create([
        'venue_id' => $venue->id,
        'name' => 'Court P2',
        'price_per_hour' => 100000,
        'status' => 'available',
    ]);

    $pastBooking = \App\Models\Booking::create([
        'user_id' => $user->id,
        'court_id' => $court->id,
        'booking_date' => now()->subDay()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'status' => 'finished',
        'payment_status' => 'paid',
        'total_amount' => 100000,
    ]);

    $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ])
        ->assertRedirect('/');

    expect(User::find($user->id))->toBeNull()
        ->and($user->fresh()->trashed())->toBeTrue()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull()
        ->and(\App\Models\Booking::find($pastBooking->id))->not->toBeNull();
});
