<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    protected $fillable = [
        'venue_id', 'name', 'type', 'price_per_hour', 'peak_price_per_hour', 'image',
        'status', 'maintenance_from', 'maintenance_to',
    ];

    protected function casts(): array
    {
        return ['price_per_hour' => 'decimal:2', 'peak_price_per_hour' => 'decimal:2'];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
