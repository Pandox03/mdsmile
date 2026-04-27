<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrestationCategory extends Model
{
    protected $fillable = ['name', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function prestations(): HasMany
    {
        return $this->hasMany(Prestation::class, 'prestation_category_id')->orderBy('order')->orderBy('name');
    }
}
