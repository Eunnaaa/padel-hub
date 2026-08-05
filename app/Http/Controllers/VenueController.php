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
        Venue::create([
            ...$request->validated(),
            'owner_id' => Auth::user()->isAdmin() ? null : Auth::id(),
        ]);

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

        $venue->update($request->validated());

        return to_route('venues.index')->with('success', 'Venue updated.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorizeVenue($venue);

        $venue->delete();

        return to_route('venues.index')->with('success', 'Venue deleted.');
    }

    private function authorizeVenue(Venue $venue): void
    {
        abort_unless(Auth::user()->isAdmin() || $venue->isOwnedBy(Auth::user()), 403);
    }
}
