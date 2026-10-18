<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicVenueController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:indoor,outdoor'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $venues = Venue::query()
            ->with(['courts' => fn ($q) => $q->where('status', '!=', 'inactive')->orderBy('price_per_hour')])
            ->where('status', 'approved')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q
                ->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")))
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->whereHas('courts', fn ($court) => $court->where('type', $type)))
            ->when($filters['max_price'] ?? null, fn ($q, $maxPrice) => $q->whereHas('courts', fn ($court) => $court->where('price_per_hour', '<=', $maxPrice)))
            ->orderBy('name')
            ->get()
            ->map(fn (Venue $venue) => [
                'id' => $venue->id,
                'slug' => $venue->slug,
                'name' => $venue->name,
                'description' => $venue->description,
                'address' => $venue->address,
                'city' => $venue->city,
                'rating' => $venue->rating,
                'settings' => $venue->settings,
                'court_count' => $venue->courts->count(),
                'min_price' => $venue->courts->min('price_per_hour'),
                'court_types' => $venue->courts->pluck('type')->unique()->values(),
            ]);

        return Inertia::render('Venues/PublicIndex', [
            'venues' => $venues,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'city' => $filters['city'] ?? '',
                'type' => $filters['type'] ?? '',
                'max_price' => $filters['max_price'] ?? '',
            ],
            'cities' => Venue::where('status', 'approved')->distinct()->orderBy('city')->pluck('city')->values(),
        ]);
    }

    public function show(Request $request, Venue $venue): Response
    {
        abort_unless($venue->status === 'approved', 404);

        $date = $request->query('date', today()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $date = today()->toDateString();
        }

        $venue->load([
            'courts' => fn ($q) => $q->where('status', '!=', 'inactive')->orderBy('name'),
            'courts.venue:id,name,city,slug,rating',
        ]);

        $courtIds = $venue->courts->pluck('id');
        $bookings = Booking::whereIn('court_id', $courtIds)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->liveLock()
            ->get(['court_id', 'start_time', 'end_time', 'status']);

        return Inertia::render('Venues/Show', [
            'venue' => $venue,
            'bookings' => $bookings,
            'selectedDate' => $date,
            'todayDate' => today()->toDateString(),
        ]);
    }
}
