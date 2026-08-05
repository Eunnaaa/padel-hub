<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ExpireBookingLocks extends Command
{
    protected $signature = 'bookings:expire-locks';

    protected $description = 'Expire pending bookings whose 5-minute payment lock has elapsed';

    public function handle(): int
    {
        Booking::where('status', 'pending')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('locked_until')
            ->where('locked_until', '<', now())
            ->update(['status' => 'expired']);

        return self::SUCCESS;
    }
}
