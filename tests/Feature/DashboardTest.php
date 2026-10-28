<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('player dapat membuka dashboard dengan aman tanpa crash walau parameter date tidak valid', function () {
    $player = User::factory()->create(['role' => 'player']);

    // Valid dashboard request
    $this->actingAs($player)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('upcoming')
            ->where('selectedDate', now()->toDateString()));

    // Malformed date query param (should fallback gracefully to today and not throw 500)
    $this->actingAs($player)->get('/dashboard?date=invalid-sql-date-format')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('selectedDate', now()->toDateString()));
});
