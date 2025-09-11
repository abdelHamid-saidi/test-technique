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
     * Accepte les événements qui se chevauchent avec la plage de dates donnée.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  DateTime  $startsAt
     * @param  DateTime  $endsAt
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsBetween($query, $startsAt = null, $endsAt = null)
    {
        if ($startsAt && $endsAt) {
            // Événements qui commencent dans la plage OU se terminent dans la plage
            $query->whereBetween('events.starts_at', [$startsAt, $endsAt])
                ->orWhereBetween('events.ends_at', [$startsAt, $endsAt]);
        } elseif ($startsAt) {
            // Événements qui commencent après cette date OU se terminent après cette date
            $query->where('events.starts_at', '>=', $startsAt)
                ->orWhere('events.ends_at', '>=', $startsAt);
        } elseif ($endsAt) {
            // Événements qui se terminent avant cette date OU commencent avant cette date
            $query->where('events.ends_at', '<=', $endsAt)
                ->orWhere('events.starts_at', '<=', $endsAt);
        }

        return $query;
    }

    // Trie les événements par date de début (croissant)
    public function scopeOrderByDate($query)
    {
        return $query->orderBy('starts_at', 'desc');
    }
}
