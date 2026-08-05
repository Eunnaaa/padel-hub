<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Promo;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $base = fn ($q) => $q->where('user_id', Auth::id());

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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'upcoming' => $upcoming,
            'promos' => Promo::active()->orderByDesc('ends_at')->limit(3)->get(),
            'notifications' => Auth::user()->notifications()->limit(5)->get(),
            'unreadNotifications' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
