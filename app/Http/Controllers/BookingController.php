<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => Booking::with(['court.venue', 'user'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Bookings/Create', [
            'courts' => Court::with('venue')->where('status', 'available')->orderBy('name')->get(),
        ]);
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $court = Court::findOrFail($data['court_id']);
        $recurrence = $data['recurrence_rule'] ?? null;

        $dates = $recurrence === 'weekly'
            ? collect(range(0, 3))->map(fn (int $i) => Carbon::parse($data['booking_date'])->addWeeks($i))
            : collect([Carbon::parse($data['booking_date'])]);

        foreach ($dates as $date) {
            $day = $date->toDateString();
            $overlap = Booking::where('court_id', $court->id)
                ->whereDate('booking_date', $day)
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->liveLock()
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($overlap) {
                return back()->withErrors(['start_time' => "Slot already booked on {$day}."])->withInput();
            }
        }

        $group = $recurrence ? (string) Str::uuid() : null;
        $hours = Carbon::parse($data['start_time'])->floatDiffInHours(Carbon::parse($data['end_time']));
        $amount = round($hours * (float) $court->price_per_hour, 2);

        $dates->each(function (Carbon $date) use ($data, $group, $recurrence, $amount): void {
            Booking::create([
                ...$data,
                'user_id' => Auth::id(),
                'booking_date' => $date->toDateString(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => $amount,
                'locked_until' => now()->addMinutes(5),
                'recurrence_rule' => $recurrence,
                'recurring_group_id' => $group,
            ]);
        });

        Auth::user()->notify(new BookingNotification(
            'Booking dibuat',
            $recurrence ? "{$dates->count()}x booking lapangan {$court->name} berulang mingguan dibuat." : "Booking lapangan {$court->name} menunggu pembayaran.",
            route('bookings.index'),
        ));

        return to_route('bookings.index')->with('success', $recurrence ? 'Recurring booking created.' : 'Booking created.');
    }

    public function edit(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('court.venue'),
            'courts' => Court::with('venue')->where('status', 'available')->orderBy('name')->get(),
        ]);
    }

    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $data = $request->validated();
        $court = Court::findOrFail($data['court_id']);

        $overlap = Booking::where('court_id', $court->id)
            ->whereDate('booking_date', $data['booking_date'])
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->liveLock()
            ->where('id', '!=', $booking->id)
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_time' => 'Slot already booked.'])->withInput();
        }

        $hours = Carbon::parse($data['start_time'])->floatDiffInHours(Carbon::parse($data['end_time']));

        $booking->update([
            ...$data,
            'total_amount' => round($hours * (float) $court->price_per_hour, 2),
        ]);

        return to_route('bookings.index')->with('success', 'Booking updated.');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        if ($request->boolean('cancel_series') && $booking->recurring_group_id) {
            Booking::where('recurring_group_id', $booking->recurring_group_id)
                ->where('booking_date', '>=', $booking->booking_date)
                ->update(['status' => 'cancelled']);

            return to_route('bookings.index')->with('success', 'Recurring series cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return to_route('bookings.index')->with('success', 'Booking cancelled.');
    }
}
