<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Promo;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isVenueOwner()) {
            return redirect()->route('venue-dashboard.index');
        }

        $base = fn ($q) => $q->where('user_id', $user->id);

        $stats = [
            'total' => (clone $base)(Booking::query())->count(),
            'upcoming' => (clone $base)(Booking::query())
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('booking_date', '>=', today())
                ->count(),
            'completed' => (clone $base)(Booking::query())->where('status', 'finished')->count(),
            'cancelled' => (clone $base)(Booking::query())->where('status', 'cancelled')->count(),
        ];

        $upcoming = (clone $base)(Booking::query())
            ->with('court.venue')
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('booking_date', '>=', today())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $selectedDate = $request->query('date', now()->toDateString());
        if (! is_string($selectedDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = now()->toDateString();
        }
        $todayDate = now()->toDateString();

        $courtBookings = Booking::query()
            ->where('booking_date', $selectedDate)
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                    ->orWhere(function ($sq) {
                        $sq->where('status', 'pending')
                            ->liveLock();
                    });
            })
            ->get(['court_id', 'start_time', 'end_time', 'status']);

        $featuredCourts = Court::with(['venue:id,name,city,slug,rating'])
            ->where('status', '!=', 'inactive')
            ->whereHas('venue', fn ($q) => $q->where('status', 'approved'))
            ->orderBy('venue_id')
            ->orderBy('name')
            ->get(['id', 'venue_id', 'name', 'type', 'price_per_hour', 'status', 'maintenance_from', 'maintenance_to']);

        $featuredVenues = Venue::where('status', 'approved')
            ->withCount('courts')
            ->limit(3)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'upcoming' => $upcoming,
            'promos' => Promo::active()->orderByDesc('ends_at')->limit(3)->get(),
            'notifications' => $user->notifications()->limit(5)->get(),
            'unreadNotifications' => $user->unreadNotifications()->count(),
            'featuredVenues' => $featuredVenues,
            'selectedDate' => $selectedDate,
            'todayDate' => $todayDate,
            'courtBookings' => $courtBookings,
            'featuredCourts' => $featuredCourts,
        ]);
    }
}
