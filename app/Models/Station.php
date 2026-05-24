<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Station extends Model
{
    protected $fillable = [
        'mimit_id', 'manager', 'flag', 'type',
        'name', 'address', 'municipality', 'province',
        'lat', 'lng',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    // ─── Relazioni ────────────────────────────────────────────────

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function selfPrices(): HasMany
    {
        return $this->hasMany(Price::class)->where('is_self', true);
    }

    public function servedPrices(): HasMany
    {
        return $this->hasMany(Price::class)->where('is_self', false);
    }

    public function priceHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(PriceHistory::class);
}


    // ─── Scope filtri ─────────────────────────────────────────────

    public function scopeInProvince(Builder $query, string $province): Builder
    {
        return $query->where('province', strtoupper($province));
    }

    public function scopeInMunicipality(Builder $query, string $municipality): Builder
    {
        return $query->where('municipality', $municipality);
    }

    /**
     * Scope: stazioni che hanno almeno un prezzo per il carburante indicato.
     * Ordina già per prezzo crescente (self service prioritario).
     */
    public function scopeWithFuelPrice(Builder $query, string $fuelType, bool $isSelf = true): Builder
    {
        return $query
            ->join('prices', 'stations.id', '=', 'prices.station_id')
            ->where('prices.fuel_type', $fuelType)
            ->where('prices.is_self', $isSelf)
            ->select('stations.*', 'prices.price', 'prices.reported_at', 'prices.reference_date')
            ->orderBy('prices.price', 'asc');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Freschezza del dato: verde/giallo/rosso in base all'ultimo aggiornamento.
     */
    public function freshnessStatus(): string
    {
        if (!isset($this->reported_at)) {
            return 'unknown';
        }

        $hours = now()->diffInHours($this->reported_at);

        return match (true) {
            $hours <= 24  => 'green',
            $hours <= 48  => 'yellow',
            default       => 'red',
        };
    }

    /**
     * Label human-readable del gestore (pulizia nomi MIMIT).
     */
    public function managerLabel(): string
    {
        return ucwords(strtolower(trim($this->flag ?: $this->manager)));
    }

    /**
     * Indirizzo formattato per la UI.
     */
    public function fullAddress(): string
    {
        return trim("{$this->address}, {$this->municipality} ({$this->province})");
    }
}
