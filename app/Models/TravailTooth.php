<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravailTooth extends Model
{
    protected $table = 'travail_teeth';

    protected $fillable = [
        'travail_id',
        'tooth_number',
        'prestation_id',
        'phase',
        'stock_id',
        'quantity',
    ];

    protected $casts = [
        'tooth_number' => 'integer',
        'phase' => 'integer',
        'quantity' => 'decimal:2',
    ];

    public function travail(): BelongsTo
    {
        return $this->belongsTo(Travail::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public static function toothNumbersUpper(): array
    {
        return range(1, 16);
    }

    public static function toothNumbersLower(): array
    {
        return range(17, 32);
    }
}
