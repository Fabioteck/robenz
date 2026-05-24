<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    protected $fillable = ['station_id', 'fuel_type', 'is_self', 'price', 'recorded_at'];

    protected $casts = [
        'is_self' => 'boolean',
        'price' => 'float',
        'recorded_at' => 'date',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
