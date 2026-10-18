<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->query('date', today()->toDateString());
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $date = today()->toDateString();
        }

        $bookings = Booking::where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->liveLock()
            ->get(['court_id', 'start_time', 'end_time', 'status']);

        // ponytail: static fetch + client polling — swap to Reverb broadcast when Redis ready
        return Inertia::render('Availability/Index', [
            'date' => $date,
            'venues' => Venue::where('status', 'approved')->orderBy('name')->get(['id', 'name', 'city']),
            'courts' => Court::with('venue:id,name,city')
                ->where('status', '!=', 'inactive')
                ->whereHas('venue', fn ($q) => $q->where('status', 'approved'))
                ->orderBy('name')
                ->get(),
            'bookings' => $bookings,
        ]);
    }
}
