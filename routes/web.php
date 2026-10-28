<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicVenueController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\VenueDashboardController;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    $date = $request->query('date', today()->toDateString());
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
        $date = today()->toDateString();
    }

    $bookings = Booking::where('booking_date', $date)
        ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
        ->liveLock()
        ->get(['court_id', 'start_time', 'end_time', 'status']);

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'stats' => [
            'venues' => Venue::count(),
            'courts' => Court::count(),
            'bookings' => Booking::count(),
            'cities' => Venue::distinct('city')->count(),
        ],
        'selectedDate' => $date,
        'todayDate' => today()->toDateString(),
        'bookings' => $bookings,
        'featuredCourts' => Court::with('venue')
            ->where('status', '!=', 'inactive')
            ->whereHas('venue', fn ($q) => $q->where('status', 'approved'))
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (Court $court) => [
                'id' => $court->id,
                'name' => $court->name,
                'type' => $court->type,
                'price_per_hour' => $court->price_per_hour,
                'status' => $court->status,
                'maintenance_from' => $court->maintenance_from,
                'maintenance_to' => $court->maintenance_to,
                'venue' => [
                    'id' => $court->venue->id,
                    'name' => $court->venue->name,
                    'city' => $court->venue->city,
                    'slug' => $court->venue->slug,
                    'rating' => $court->venue->rating,
                ],
            ]),
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/explore', [PublicVenueController::class, 'index'])->name('venues.explore');
Route::get('/explore/{venue:slug}', [PublicVenueController::class, 'show'])->name('venues.show');

Route::post('payments/notification', [PaymentController::class, 'notification'])->name('payments.notification');

Route::middleware('auth')->group(function () {
    Route::resource('bookings', BookingController::class)->except(['show']);
    Route::post('bookings/{booking}/apply-promo', [PaymentController::class, 'applyPromo'])
        ->name('payments.apply-promo')
        ->middleware('throttle:15,1');
    Route::delete('bookings/{booking}/remove-promo', [PaymentController::class, 'removePromo'])
        ->name('payments.remove-promo');
    Route::get('bookings/{booking}/pay', [PaymentController::class, 'create'])
        ->name('payments.create');
    Route::post('bookings/{booking}/pay', [PaymentController::class, 'store'])
        ->name('payments.store')
        ->middleware('throttle:15,1');
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{booking}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');

    Route::middleware('venue')->group(function () {
        Route::resource('venues', VenueController::class)->except(['show']);
        Route::resource('courts', CourtController::class)->except(['show']);
        Route::get('checkin', [CheckinController::class, 'index'])->name('checkin.index');
        Route::patch('checkin/{booking}', [CheckinController::class, 'update'])->name('checkin.update');
        Route::get('checkin/{booking}/scan', [CheckinController::class, 'scan'])->name('checkin.scan');
        Route::get('venue-dashboard', [VenueDashboardController::class, 'index'])->name('venue-dashboard.index');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::post('courts/{court}/toggle-status', [AdminController::class, 'toggleCourtStatus'])->name('courts.toggle-status');
        Route::post('venues/{venue}/approve', [AdminController::class, 'approveVenue'])->name('venues.approve');
        Route::post('venues/{venue}/reject', [AdminController::class, 'rejectVenue'])->name('venues.reject');
        Route::post('users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
