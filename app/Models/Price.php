<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'station_id', 'fuel_type', 'price', 'is_self', 'reported_at', 'reference_date',
    ];

    protected $casts = [
        'price'          => 'decimal:3',
        'is_self'        => 'boolean',
        'reported_at'    => 'datetime',
        'reference_date' => 'date',
    ];

    // Carburanti standard MIMIT
    const FUEL_TYPES = [
        'Benzina'   => '🟢',
        'Gasolio'   => '🔵',
        'GPL'       => '🟣',
        'Metano'    => '🟠',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Prezzo formattato con € e 3 decimali.
     */
    public function formattedPrice(): string
    {
        return '€ ' . number_format($this->price, 3, ',', '.');
    }

    /**
     * Emoji per il tipo di carburante.
     */
    public function fuelEmoji(): string
    {
        return self::FUEL_TYPES[$this->fuel_type] ?? '⚪';
    }

    /**
     * Self o Servito.
     */
    public function modeLabel(): string
    {
        return $this->is_self ? 'Self' : 'Servito';
    }
}
