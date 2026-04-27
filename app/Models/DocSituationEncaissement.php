<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocSituationEncaissement extends Model
{
    protected $fillable = [
        'doc_id',
        'year',
        'month',
        'montant',
        'paid_on',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'montant' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }
}
