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
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'stats' => [
        'venues' => Venue::count(),
        'courts' => Court::count(),
        'bookings' => Booking::count(),
        'cities' => Venue::distinct('city')->count(),
    ],
]));

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/explore', [PublicVenueController::class, 'index'])->name('venues.explore');
Route::get('/explore/{venue:slug}', [PublicVenueController::class, 'show'])->name('venues.show');

Route::post('payments/notification', [PaymentController::class, 'notification'])->name('payments.notification');

Route::middleware('auth')->group(function () {
    Route::resource('bookings', BookingController::class)->except(['show']);
    Route::get('bookings/{booking}/pay', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('bookings/{booking}/pay', [PaymentController::class, 'store'])->name('payments.store');
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
        Route::post('venues/{venue}/approve', [AdminController::class, 'approveVenue'])->name('venues.approve');
        Route::post('venues/{venue}/reject', [AdminController::class, 'rejectVenue'])->name('venues.reject');
        Route::post('users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
