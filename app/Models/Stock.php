<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'name',
        'reference',
        'quantity',
        'seuil_alerte_min',
        'unite',
        'fournisseur_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'seuil_alerte_min' => 'decimal:2',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function travailTeeth(): HasMany
    {
        return $this->hasMany(TravailTooth::class);
    }

    public function decrementQuantity(float $amount = 1): void
    {
        $this->decrement('quantity', $amount);
    }

    public function hasEnough(float $amount = 1): bool
    {
        return $this->quantity >= $amount;
    }
}
