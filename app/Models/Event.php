<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    /**
     * les attributs qui peuvent être assignés en masse
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'starts_at',
        'ends_at',
        'user_id',
    ];

    /**
     * les attributs qui doivent être castés
     *
     * @var string[]
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Gets the user the event belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Limite une requête pour filtrer les événements par dates flexibles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  DateTime  $startsAt
     * @param  DateTime  $endsAt
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsBetween($query, $startsAt = null, $endsAt = null)
    {
        if ($startsAt) {
            $query->where('events.starts_at', '>', $startsAt);
        }
        if ($endsAt) {
            // remplace starts_at par ends_at
            $query->where('events.ends_at', '<', $endsAt);
        }
        return $query;
    }
}
