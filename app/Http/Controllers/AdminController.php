<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'users' => User::count(),
            'pending_venues' => Venue::where('status', 'pending')->count(),
            'approved_venues' => Venue::where('status', 'approved')->count(),
            'total_courts' => Court::count(),
            'available_courts' => Court::where('status', 'available')->count(),
            'maintenance_courts' => Court::where('status', 'maintenance')->count(),
            'bookings' => Booking::count(),
            'revenue' => Booking::whereNotIn('status', ['cancelled', 'expired'])
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'today_bookings' => Booking::whereDate('booking_date', today())->count(),
        ];

        $courts = Court::with(['venue:id,name,city,slug'])
            ->withCount(['bookings as today_bookings_count' => function ($q) {
                $q->whereDate('booking_date', today())
                    ->whereIn('status', ['confirmed', 'checked_in', 'pending']);
            }])
            ->orderBy('venue_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'courts' => $courts,
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
            'pendingVenues' => Venue::where('status', 'pending')->latest()->get(),
            'recentBookings' => Booking::with(['court.venue', 'user'])
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function toggleCourtStatus(Court $court): RedirectResponse
    {
        $newStatus = $court->status === 'available' ? 'maintenance' : 'available';
        $court->update(['status' => $newStatus]);
        $court->loadMissing('venue');
        $venueName = $court->venue?->name ?? 'Venue';

        return back()->with('success', "Status lapangan {$court->name} ({$venueName}) berhasil diubah menjadi {$newStatus}.");
    }

    public function approveVenue(Venue $venue): RedirectResponse
    {
        $venue->update(['status' => 'approved']);

        return back()->with('success', "Venue {$venue->name} disetujui.");
    }

    public function rejectVenue(Venue $venue): RedirectResponse
    {
        $venue->update(['status' => 'rejected']);

        return back()->with('success', "Venue {$venue->name} ditolak.");
    }

    public function toggleUser(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Tidak bisa suspend admin.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? "User {$user->name} diaktifkan." : "User {$user->name} di-suspend.");
    }
}
