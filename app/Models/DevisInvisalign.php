<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevisInvisalign extends Model
{
    protected $table = 'devis_invisalign';

    protected $fillable = [
        'numero',
        'date_devis',
        'doc_id',
        'client_nom',
        'patient_nom',
        'opt_maxillaire',
        'opt_mandibulaire',
        'taquets',
        'stripping',
        'conditions_paiement',
        'banque_ligne',
        'beneficiaire',
        'rib',
        'commandes_email',
        'societe_footer',
        'total_ttc',
    ];

    protected function casts(): array
    {
        return [
            'date_devis' => 'date',
            'opt_maxillaire' => 'boolean',
            'opt_mandibulaire' => 'boolean',
            'taquets' => 'boolean',
            'stripping' => 'boolean',
            'total_ttc' => 'decimal:2',
        ];
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(DevisInvisalignLigne::class, 'devis_invisalign_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(DevisInvisalignPaiement::class, 'devis_invisalign_id')
            ->orderBy('date_paiement')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function recalculeTotal(): void
    {
        $sum = (float) $this->lignes()->sum('total_ttc');
        $this->update(['total_ttc' => round($sum, 2)]);
    }
}
