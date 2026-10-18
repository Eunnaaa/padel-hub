<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Promo;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@padelhub.id'],
            ['name' => 'Admin PadelHub', 'password' => 'password', 'role' => 'admin'],
        );

        $owner = User::updateOrCreate(
            ['email' => 'owner@padelhub.id'],
            ['name' => 'Owner PadelHub', 'password' => 'password', 'role' => 'venue_owner'],
        );

        $venue = Venue::updateOrCreate(
            ['slug' => 'padelhub-center'],
            [
                'name' => 'PadelHub Center',
                'description' => 'Indoor padel venue premium di pusat kota.',
                'address' => 'Jl. Sudirman No. 1',
                'city' => 'Jakarta',
                'status' => 'approved',
                'owner_id' => $owner->id,
                'settings' => ['amenities' => ['parkir', 'kafe', 'shower']],
            ],
        );

        $venue2 = Venue::updateOrCreate(
            ['slug' => 'padel-bumi-sangkuriang'],
            [
                'name' => 'Padel Bumi Sangkuriang',
                'description' => 'Venue outdoor dengan suasana sejuk pegunungan.',
                'address' => 'Jl. Bumi Sangkuriang No. 2',
                'city' => 'Bandung',
                'status' => 'approved',
                'owner_id' => $owner->id,
                'settings' => ['amenities' => ['parkir', 'kafe', 'loker']],
            ],
        );

        $venue3 = Venue::updateOrCreate(
            ['slug' => 'padel-arena-senayan'],
            [
                'name' => 'Padel Arena Senayan',
                'description' => 'Venue padel panoramic glass standar internasional di kawasan olahraga Senayan.',
                'address' => 'Jl. Pintu Satu Senayan No. 8',
                'city' => 'Jakarta',
                'status' => 'approved',
                'owner_id' => $owner->id,
                'settings' => ['amenities' => ['parkir', 'shower', 'kafe', 'pro shop']],
            ],
        );

        Venue::updateOrCreate(
            ['slug' => 'padelhub-surabaya'],
            [
                'name' => 'PadelHub Surabaya',
                'description' => 'Kandidat venue baru, menunggu review admin.',
                'address' => 'Jl. Darmo No. 3',
                'city' => 'Surabaya',
                'status' => 'pending',
                'owner_id' => $owner->id,
            ],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue->id, 'name' => 'Court 1'],
            ['price_per_hour' => 150000, 'status' => 'available', 'type' => 'indoor'],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue->id, 'name' => 'Court 2'],
            ['price_per_hour' => 150000, 'status' => 'available', 'type' => 'indoor'],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue2->id, 'name' => 'Court A'],
            ['price_per_hour' => 120000, 'status' => 'available', 'type' => 'outdoor'],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue2->id, 'name' => 'Court B'],
            ['price_per_hour' => 120000, 'status' => 'maintenance', 'type' => 'outdoor'],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue3->id, 'name' => 'Court Senayan 1'],
            ['price_per_hour' => 160000, 'status' => 'available', 'type' => 'indoor'],
        );

        Court::updateOrCreate(
            ['venue_id' => $venue3->id, 'name' => 'Court Senayan 2'],
            ['price_per_hour' => 160000, 'status' => 'available', 'type' => 'indoor'],
        );

        $user = User::updateOrCreate(
            ['email' => 'player@example.com'],
            ['name' => 'Player Demo', 'password' => 'password', 'role' => 'player'],
        );

        $court1 = Court::where('venue_id', $venue->id)->where('name', 'Court 1')->firstOrFail();
        $court2 = Court::where('venue_id', $venue->id)->where('name', 'Court 2')->firstOrFail();

        $bookings = [
            [
                'user_id' => $user->id,
                'court_id' => $court1->id,
                'booking_date' => now()->toDateString(),
                'start_time' => '18:00',
                'end_time' => '19:00',
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'total_amount' => 150000,
            ],
            [
                'user_id' => $user->id,
                'court_id' => $court2->id,
                'booking_date' => now()->toDateString(),
                'start_time' => '19:00',
                'end_time' => '20:00',
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => 150000,
                'locked_until' => now()->addMinutes(5),
            ],
        ];

        foreach ($bookings as $data) {
            $b = Booking::updateOrCreate(
                array_intersect_key($data, array_flip(['user_id', 'court_id', 'booking_date', 'start_time', 'end_time'])),
                array_diff_key($data, array_flip(['user_id', 'court_id', 'booking_date', 'start_time', 'end_time'])),
            );

            if ($b->payment_status === 'paid') {
                Payment::updateOrCreate(
                    ['booking_id' => $b->id],
                    [
                        'amount' => $b->total_amount,
                        'method' => 'qris',
                        'provider' => 'manual',
                        'status' => 'paid',
                        'transaction_reference' => 'MAN-DEMO-'.str_pad((string) $b->id, 5, '0', STR_PAD_LEFT),
                    ],
                );
            }
        }

        Promo::updateOrCreate(
            ['code' => 'PADELBARU'],
            [
                'title' => 'Diskon Pemain Baru 20%',
                'description' => 'Diskon 20% untuk pemesanan lapangan pertama kamu.',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
                'quota' => 100,
                'used_count' => 0,
                'is_active' => true,
            ],
        );

        Promo::updateOrCreate(
            ['code' => 'HEMAT25K'],
            [
                'title' => 'Potongan Langsung 25rb',
                'description' => 'Potongan langsung Rp 25.000 untuk booking apa saja.',
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
                'quota' => 50,
                'used_count' => 0,
                'is_active' => true,
            ],
        );
    }
}
