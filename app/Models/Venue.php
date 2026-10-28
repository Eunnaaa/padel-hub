<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'address', 'city', 'latitude', 'longitude', 'rating', 'status', 'settings', 'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'settings' => 'array',
        ];
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->owner_id === $user->id;
    }
}
