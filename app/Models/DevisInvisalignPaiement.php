<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevisInvisalignPaiement extends Model
{
    protected $table = 'devis_invisalign_paiements';

    protected $fillable = [
        'devis_invisalign_id',
        'sort_order',
        'date_paiement',
        'montant',
        'type_paiement',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(DevisInvisalign::class, 'devis_invisalign_id');
    }
}
