<?php

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

function publicVenue(array $overrides = []): Venue
{
    return Venue::create(array_merge([
        'name' => 'Venue '.Str::random(6),
        'slug' => 'venue-'.Str::random(6),
        'address' => 'Jl. Test',
        'city' => 'Jakarta',
        'status' => 'approved',
    ], $overrides));
}

test('explore hanya menampilkan venue yang disetujui', function () {
    $approved = publicVenue();
    publicVenue(['status' => 'pending']);
    publicVenue(['status' => 'rejected']);

    $this->get('/explore')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Venues/PublicIndex')
            ->has('venues', 1)
            ->where('venues.0.id', $approved->id));
});

test('explore memfilter berdasarkan kota', function () {
    $jakarta = publicVenue(['city' => 'Jakarta']);
    publicVenue(['city' => 'Bandung']);

    $this->get('/explore?city=Jakarta')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('venues', 1)
            ->where('venues.0.id', $jakarta->id));
});

test('explore memfilter berdasarkan tipe lapangan', function () {
    $indoor = publicVenue();
    $outdoor = publicVenue(['name' => 'Outdoor Venue']);

    Court::create(['venue_id' => $indoor->id, 'name' => 'A', 'type' => 'indoor', 'price_per_hour' => 100000, 'status' => 'available']);
    Court::create(['venue_id' => $outdoor->id, 'name' => 'B', 'type' => 'outdoor', 'price_per_hour' => 100000, 'status' => 'available']);

    $this->get('/explore?type=outdoor')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('venues', 1)
            ->where('venues.0.id', $outdoor->id));
});

test('explore memfilter berdasarkan harga maksimum', function () {
    $murah = publicVenue(['name' => 'Murah']);
    $mahal = publicVenue(['name' => 'Mahal']);

    Court::create(['venue_id' => $murah->id, 'name' => 'A', 'price_per_hour' => 50000, 'status' => 'available']);
    Court::create(['venue_id' => $mahal->id, 'name' => 'B', 'price_per_hour' => 300000, 'status' => 'available']);

    $this->get('/explore?max_price=100000')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('venues', 1)
            ->where('venues.0.id', $murah->id));
});

test('halaman detail venue hanya untuk venue disetujui', function () {
    $approved = publicVenue();
    $pending = publicVenue(['status' => 'pending']);

    $this->get("/explore/{$approved->slug}")->assertOk();
    $this->get("/explore/{$pending->slug}")->assertNotFound();
});

test('detail venue menampilkan lapangan yang tersedia', function () {
    $venue = publicVenue();
    $court = Court::create(['venue_id' => $venue->id, 'name' => 'Court 1', 'price_per_hour' => 100000, 'status' => 'available']);
    Court::create(['venue_id' => $venue->id, 'name' => 'Court 2', 'price_per_hour' => 100000, 'status' => 'inactive']);

    $this->get("/explore/{$venue->slug}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Venues/Show')
            ->has('venue.courts', 1)
            ->where('venue.courts.0.id', $court->id));
});
