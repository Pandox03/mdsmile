<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestation extends Model
{
    protected $table = 'prestations';
    protected $fillable = ['prestation_category_id', 'name', 'price', 'order'];

    protected $casts = [
        'price' => 'decimal:2',
        'order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PrestationCategory::class, 'prestation_category_id');
    }

    public function docPrestationPrices(): HasMany
    {
        return $this->hasMany(DocPrestationPrice::class, 'prestation_id');
    }

    public function getPriceDisplayAttribute(): string
    {
        return $this->price === null ? 'Sur devis' : number_format((float) $this->price, 0, ',', ' ') . ' DH';
    }
}
