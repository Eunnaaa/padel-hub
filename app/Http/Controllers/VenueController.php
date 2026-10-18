<?php

namespace App\Http\Controllers;

use App\Http\Requests\VenueRequest;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VenueController extends Controller
{
    public function index(): Response
    {
        $venues = Auth::user()->isAdmin()
            ? Venue::latest()->get()
            : Venue::where('owner_id', Auth::id())->latest()->get();

        return Inertia::render('Venues/Index', ['venues' => $venues]);
    }

    public function create(): Response
    {
        return Inertia::render('Venues/Create');
    }

    public function store(VenueRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (! Auth::user()->isAdmin()) {
            $data['status'] = 'pending';
            $data['owner_id'] = Auth::id();
        } else {
            $data['status'] = $data['status'] ?? 'approved';
            $data['owner_id'] = null;
        }

        Venue::create($data);

        return to_route('venues.index')->with('success', 'Venue created.');
    }

    public function edit(Venue $venue): Response
    {
        $this->authorizeVenue($venue);

        return Inertia::render('Venues/Edit', ['venue' => $venue]);
    }

    public function update(VenueRequest $request, Venue $venue): RedirectResponse
    {
        $this->authorizeVenue($venue);

        $data = $request->validated();
        if (! Auth::user()->isAdmin()) {
            unset($data['status'], $data['owner_id']);
        }

        $venue->update($data);

        return to_route('venues.index')->with('success', 'Venue updated.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorizeVenue($venue);

        $hasActiveBookings = $venue->courts()
            ->whereHas('bookings', fn ($q) => $q
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->whereDate('booking_date', '>=', today()))
            ->exists();

        abort_if($hasActiveBookings, 422, 'Venue tidak dapat dihapus karena masih memiliki jadwal booking aktif.');

        $venue->delete();

        return to_route('venues.index')->with('success', 'Venue deleted.');
    }

    private function authorizeVenue(Venue $venue): void
    {
        abort_unless(Auth::user()->isAdmin() || $venue->isOwnedBy(Auth::user()), 403);
    }
}
