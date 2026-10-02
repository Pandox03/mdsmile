<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonLivraison extends Model
{
    protected $table = 'bons_livraison';

    public const PAIEMENT_NON_PAYE = 'non_paye';
    public const PAIEMENT_PARTIEL = 'partiel';
    public const PAIEMENT_PAYE = 'paye';

    protected $fillable = [
        'numero',
        'date_livraison',
        'doc_id',
        'travail_id',
        'dentiste_nom',
        'patient_nom',
        'numero_fiche',
        'reference_travail',
        'details_travail',
        'dents',
        'montant',
        'paiement_statut',
        'type_paiement',
        'observations',
        'recu_par',
    ];

    protected function casts(): array
    {
        return [
            'date_livraison' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public static function paiementLabels(): array
    {
        return [
            self::PAIEMENT_NON_PAYE => 'Non payé',
            self::PAIEMENT_PARTIEL => 'Partiellement payé',
            self::PAIEMENT_PAYE => 'Payé',
        ];
    }

    public function getPaiementLabelAttribute(): string
    {
        return self::paiementLabels()[$this->paiement_statut] ?? $this->paiement_statut;
    }

    public function isPaiementEffectue(): bool
    {
        return in_array($this->paiement_statut, [self::PAIEMENT_PARTIEL, self::PAIEMENT_PAYE], true);
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function travail(): BelongsTo
    {
        return $this->belongsTo(Travail::class);
    }
}
