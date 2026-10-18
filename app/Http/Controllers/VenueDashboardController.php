<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VenueDashboardController extends Controller
{
    public function index(): Response
    {
        abort_unless(Auth::user()->isStaff(), 403);

        $scope = fn ($q) => Auth::user()->isAdmin()
            ? $q
            : $q->whereHas('court', fn ($c) => $c->whereIn('venue_id', Auth::user()->venues()->pluck('id')));

        $paid = fn ($q) => $scope((clone $q))->whereNotIn('status', ['cancelled', 'expired'])
            ->where('payment_status', 'paid');

        $revenueToday = (clone $paid)(Booking::query())->whereDate('booking_date', today())->sum('total_amount');
        $revenueWeek = (clone $paid)(Booking::query())->where('booking_date', '>=', today()->startOfWeek())->sum('total_amount');
        $revenueMonth = (clone $paid)(Booking::query())->where('booking_date', '>=', today()->startOfMonth())->sum('total_amount');

        $bookingsToday = (clone $paid)(Booking::query())->whereDate('booking_date', today())->count();
        $bookingsWeek = (clone $paid)(Booking::query())->where('booking_date', '>=', today()->startOfWeek())->count();
        $bookingsMonth = (clone $paid)(Booking::query())->where('booking_date', '>=', today()->startOfMonth())->count();

        // ponytail: hardcoded 7-day window — parametrize date range filter when UI needs it
        $trendDays = collect(CarbonPeriod::since(today()->subDays(6))->days()->until(today()))
            ->map(fn (Carbon $day) => [
                'date' => $day->format('Y-m-d'),
                'label' => $day->format('D'),
                'bookings' => (clone $paid)(Booking::query())->whereDate('booking_date', $day)->count(),
                'revenue' => (float) (clone $paid)(Booking::query())->whereDate('booking_date', $day)->sum('total_amount'),
            ])->values();

        $maxRevenue = $trendDays->max('revenue') ?: 1;
        $maxBookings = $trendDays->max('bookings') ?: 1;

        $hourExpr = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%H:00', start_time)",
            'mysql', 'mariadb' => "DATE_FORMAT(start_time, '%H:00')",
            default => "to_char(start_time, 'HH24:00')",
        };

        // ponytail: peak hours + court usage scoped to 30 days — widen when analytics filter exists
        $peakHours = $scope(Booking::query())
            ->where('booking_date', '>=', today()->subDays(30))
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->selectRaw("{$hourExpr} as hour, count(*) as total")
            ->groupBy('hour')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $courtUsage = $scope(Booking::query())
            ->with('court')
            ->where('booking_date', '>=', today()->subDays(30))
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->selectRaw('court_id, count(*) as total')
            ->groupBy('court_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['court' => $row->court?->name ?? 'Lapangan', 'total' => $row->total]);

        $venues = (Auth::user()->isAdmin() ? Venue::query() : Auth::user()->venues())
            ->withCount('courts')
            ->get();

        $recentBookings = $scope(Booking::query())
            ->with(['court.venue', 'user'])
            ->latest('id')
            ->limit(5)
            ->get();

        return Inertia::render('VenueDashboard/Index', [
            'revenue' => ['today' => $revenueToday, 'week' => $revenueWeek, 'month' => $revenueMonth],
            'bookings' => ['today' => $bookingsToday, 'week' => $bookingsWeek, 'month' => $bookingsMonth],
            'trendDays' => $trendDays,
            'maxRevenue' => $maxRevenue,
            'maxBookings' => $maxBookings,
            'peakHours' => $peakHours,
            'courtUsage' => $courtUsage,
            'venues' => $venues,
            'recentBookings' => $recentBookings,
        ]);
    }
}
