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
use Illuminate\Support\Facades\DB;
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
        $court = Court::with('venue')->findOrFail($data['court_id']);

        abort_unless($court->status === 'available' && $court->venue->status === 'approved', 422, 'Lapangan tidak tersedia untuk dipesan.');

        if ($court->maintenance_from && $court->maintenance_to) {
            if ($data['start_time'] < $court->maintenance_to && $data['end_time'] > $court->maintenance_from) {
                return back()->withErrors(['start_time' => "Lapangan sedang maintenance antara {$court->maintenance_from}–{$court->maintenance_to}."])->withInput();
            }
        }

        $recurrence = $data['recurrence_rule'] ?? null;
        $dates = $recurrence === 'weekly'
            ? collect(range(0, 3))->map(fn (int $i) => Carbon::parse($data['booking_date'])->addWeeks($i))
            : collect([Carbon::parse($data['booking_date'])]);

        return DB::transaction(function () use ($data, $dates, $court, $recurrence) {
            Court::where('id', $court->id)->lockForUpdate()->first();

            foreach ($dates as $date) {
                $day = $date->toDateString();
                $overlap = Booking::where('court_id', $court->id)
                    ->whereDate('booking_date', $day)
                    ->whereNotIn('status', ['cancelled', 'expired'])
                    ->liveLock()
                    ->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time'])
                    ->lockForUpdate()
                    ->exists();

                if ($overlap) {
                    return back()->withErrors(['start_time' => "Slot already booked on {$day}."])->withInput();
                }
            }

            $group = $recurrence ? (string) Str::uuid() : null;
            $hours = Carbon::parse($data['start_time'])->floatDiffInHours(Carbon::parse($data['end_time']));
            $amount = round($hours * (float) $court->price_per_hour, 2);

            $firstBooking = null;
            $dates->each(function (Carbon $date) use ($data, $group, $recurrence, $amount, &$firstBooking): void {
                $created = Booking::create([
                    'court_id' => $data['court_id'],
                    'booking_date' => $date->toDateString(),
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'user_id' => Auth::id(),
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'total_amount' => $amount,
                    'locked_until' => now()->addMinutes(5),
                    'recurrence_rule' => $recurrence,
                    'recurring_group_id' => $group,
                ]);

                if ($firstBooking === null) {
                    $firstBooking = $created;
                }
            });

            Auth::user()->notify(new BookingNotification(
                'Booking dibuat',
                $recurrence ? "{$dates->count()}x booking lapangan {$court->name} berulang mingguan dibuat." : "Booking lapangan {$court->name} menunggu pembayaran.",
                route('bookings.index'),
            ));

            if ($recurrence) {
                return to_route('bookings.index')->with('success', 'Recurring booking created.');
            }

            return to_route('payments.create', $firstBooking->id)->with('success', 'Booking berhasil dibuat. Silakan selesaikan pembayaran sebelum slot kedaluwarsa.');
        });
    }

    public function edit(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_if($booking->payment_status === 'paid', 422, 'Booking yang sudah dibayar tidak dapat diubah.');
        abort_if(in_array($booking->status, ['cancelled', 'expired', 'finished']), 422, 'Booking ini sudah tidak aktif.');

        if ($booking->isLockExpired() && $booking->status === 'pending') {
            $booking->update(['status' => 'expired']);
            abort(422, 'Waktu pemesanan telah kedaluwarsa.');
        }

        return Inertia::render('Bookings/Edit', [
            'booking' => $booking->load('court.venue'),
            'courts' => Court::with('venue')->where('status', 'available')->orderBy('name')->get(),
        ]);
    }

    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_if($booking->payment_status === 'paid', 422, 'Booking yang sudah dibayar tidak dapat diubah.');
        abort_if(in_array($booking->status, ['cancelled', 'expired', 'finished']), 422, 'Booking ini sudah tidak aktif.');

        if ($booking->isLockExpired() && $booking->status === 'pending') {
            $booking->update(['status' => 'expired']);
            abort(422, 'Waktu pemesanan telah kedaluwarsa.');
        }

        $data = $request->validated();
        $court = Court::with('venue')->findOrFail($data['court_id']);

        abort_unless($court->status === 'available' && $court->venue->status === 'approved', 422, 'Lapangan tidak tersedia.');

        if ($court->maintenance_from && $court->maintenance_to) {
            if ($data['start_time'] < $court->maintenance_to && $data['end_time'] > $court->maintenance_from) {
                return back()->withErrors(['start_time' => "Lapangan sedang maintenance antara {$court->maintenance_from}–{$court->maintenance_to}."])->withInput();
            }
        }

        return DB::transaction(function () use ($data, $booking, $court) {
            Court::where('id', $court->id)->lockForUpdate()->first();

            $overlap = Booking::where('court_id', $court->id)
                ->whereDate('booking_date', $data['booking_date'])
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->liveLock()
                ->where('id', '!=', $booking->id)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->lockForUpdate()
                ->exists();

            if ($overlap) {
                return back()->withErrors(['start_time' => 'Slot already booked.'])->withInput();
            }

            $hours = Carbon::parse($data['start_time'])->floatDiffInHours(Carbon::parse($data['end_time']));

            $booking->update([
                'court_id' => $data['court_id'],
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'recurrence_rule' => $data['recurrence_rule'] ?? null,
                'total_amount' => round($hours * (float) $court->price_per_hour, 2),
            ]);

            return to_route('bookings.index')->with('success', 'Booking updated.');
        });
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_if(in_array($booking->status, ['checked_in', 'finished', 'cancelled', 'expired']), 422, 'Booking tidak dapat dibatalkan.');
        abort_if($booking->payment_status === 'paid', 422, 'Booking yang sudah dibayar tidak dapat dibatalkan langsung. Silakan hubungi pengelola venue.');

        if ($request->boolean('cancel_series') && $booking->recurring_group_id) {
            Booking::where('recurring_group_id', $booking->recurring_group_id)
                ->where('booking_date', '>=', $booking->booking_date)
                ->where('payment_status', '!=', 'paid')
                ->whereNotIn('status', ['checked_in', 'finished', 'cancelled', 'expired'])
                ->update(['status' => 'cancelled']);

            return to_route('bookings.index')->with('success', 'Recurring series cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return to_route('bookings.index')->with('success', 'Booking cancelled.');
    }
}
