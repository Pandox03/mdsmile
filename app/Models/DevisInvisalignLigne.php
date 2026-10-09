<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevisInvisalignLigne extends Model
{
    protected $table = 'devis_invisalign_lignes';

    protected $fillable = [
        'devis_invisalign_id',
        'sort_order',
        'designation',
        'quantite',
        'arcade',
        'prix_unitaire',
        'total_ttc',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'quantite' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'total_ttc' => 'decimal:2',
        ];
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(DevisInvisalign::class, 'devis_invisalign_id');
    }
}
