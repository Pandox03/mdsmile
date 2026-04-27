<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocPrestationPrice extends Model
{
    protected $table = 'doc_prestation_prices';

    protected $fillable = ['doc_id', 'prestation_id', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }
}
