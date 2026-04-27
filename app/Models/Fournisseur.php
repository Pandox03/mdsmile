<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    protected $table = 'fournisseurs';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'adresse',
    ];

    public function stockItems(): HasMany
    {
        return $this->hasMany(Stock::class, 'fournisseur_id');
    }
}
