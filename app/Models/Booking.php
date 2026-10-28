<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'court_id', 'booking_date', 'start_time', 'end_time',
        'status', 'payment_status', 'total_amount', 'locked_until',
        'recurrence_rule', 'recurring_group_id', 'promo_id', 'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'locked_until' => 'datetime',
        ];
    }

    public function scopeLiveLock($query): void
    {
        $query->where(fn ($q) => $q->whereNull('locked_until')->orWhere('locked_until', '>=', now()));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isLockExpired(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isPast();
    }
}
