<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourtRequest;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CourtController extends Controller
{
    public function index(): Response
    {
        $courts = Court::with('venue');

        if (! Auth::user()->isAdmin()) {
            $courts->whereIn('venue_id', Auth::user()->venues()->pluck('id'));
        }

        return Inertia::render('Courts/Index', ['courts' => $courts->latest()->get()]);
    }

    public function create(): Response
    {
        abort_unless(Auth::user()->isAdmin() || Auth::user()->venues()->exists(), 403, 'Buat venue dulu.');

        $venues = $this->managableVenues()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Courts/Create', ['venues' => $venues]);
    }

    public function store(CourtRequest $request): RedirectResponse
    {
        $this->assertVenueManagable((int) $request->validated('venue_id'));

        Court::create($request->validated());

        return to_route('courts.index')->with('success', 'Court created.');
    }

    public function edit(Court $court): Response
    {
        $this->authorizeCourt($court);

        return Inertia::render('Courts/Edit', [
            'court' => $court,
            'venues' => $this->managableVenues()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(CourtRequest $request, Court $court): RedirectResponse
    {
        $this->authorizeCourt($court);
        $this->assertVenueManagable((int) $request->validated('venue_id'));

        $court->update($request->validated());

        return to_route('courts.index')->with('success', 'Court updated.');
    }

    public function destroy(Court $court): RedirectResponse
    {
        $this->authorizeCourt($court);

        $hasActiveBookings = $court->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->whereDate('booking_date', '>=', today())
            ->exists();

        abort_if($hasActiveBookings, 422, 'Lapangan tidak dapat dihapus karena memiliki jadwal booking aktif. Silakan ubah status lapangan menjadi nonaktif.');

        $court->delete();

        return to_route('courts.index')->with('success', 'Court deleted.');
    }

    private function managableVenues(): Builder
    {
        $venues = Venue::query();

        if (! Auth::user()->isAdmin()) {
            $venues->where('owner_id', Auth::id());
        }

        return $venues;
    }

    private function assertVenueManagable(int $venueId): void
    {
        abort_unless($this->managableVenues()->whereKey($venueId)->exists(), 403);
    }

    private function authorizeCourt(Court $court): void
    {
        abort_unless(Auth::user()->isAdmin() || $court->venue->isOwnedBy(Auth::user()), 403);
    }
}
