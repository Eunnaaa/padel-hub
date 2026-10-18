<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckinRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CheckinController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Checkin/Index', [
            'bookings' => Booking::with(['court.venue', 'user', 'payment'])
                ->whereDate('booking_date', Carbon::today())
                ->when(! Auth::user()->isAdmin(), fn ($q) => $q->whereHas('court', fn ($c) => $c->whereIn('venue_id', Auth::user()->venues()->pluck('id'))))
                ->orderBy('start_time')
                ->get(),
        ]);
    }

    public function update(CheckinRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($this->managesBooking($booking), 403);
        abort_unless($booking->payment_status === 'paid', 422, 'Not paid yet.');
        abort_if(in_array($booking->status, ['cancelled', 'expired']), 422, 'Booking sudah dibatalkan atau kedaluwarsa.');

        $booking->update(['status' => $request->validated('status')]);

        return back()->with('success', 'Status updated.');
    }

    public function scan(Booking $booking): Response
    {
        abort_unless($this->managesBooking($booking), 403);
        abort_unless($booking->payment_status === 'paid', 403, 'Belum dibayar.');
        abort_if(in_array($booking->status, ['cancelled', 'expired']), 422, 'Tiket booking ini sudah dibatalkan atau kedaluwarsa.');

        return Inertia::render('Checkin/Scan', [
            'booking' => $booking->load('court.venue', 'user', 'payment'),
        ]);
    }

    private function managesBooking(Booking $booking): bool
    {
        return Auth::user()->isAdmin() || $booking->court->venue->isOwnedBy(Auth::user());
    }
}
