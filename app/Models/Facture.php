<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facture extends Model
{
    protected $table = 'factures';

    public const STATUT_PAYEE = 'payee';
    public const STATUT_NON_PAYEE = 'non_payee';

    protected $fillable = [
        'doc_id',
        'numero',
        'date_facture',
        'montant_comptabilise',
        'comptabilise_dans_restant',
        'split_montants',
        'internes_token',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'montant_comptabilise' => 'decimal:2',
        'comptabilise_dans_restant' => 'boolean',
        'split_montants' => 'array',
    ];

    public static function statutPaiementLabels(): array
    {
        return [
            self::STATUT_PAYEE => 'Payée',
            self::STATUT_NON_PAYEE => 'Non payée',
        ];
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function travaux(): BelongsToMany
    {
        return $this->belongsToMany(Travail::class, 'facture_travail')
            ->withPivot('prix_comptabilise')
            ->withTimestamps();
    }

    /** Total facture = sum of amounts (pivot) for this facture, or montant_comptabilise for manual factures without travaux. */
    public function getTotalFactureAttribute(): float
    {
        if ($this->relationLoaded('travaux') && $this->travaux->isNotEmpty()) {
            return (float) $this->travaux->sum(fn ($t) => (float) ($t->pivot->prix_comptabilise ?? $t->prix_dhs));
        }

        // Manual facture without travaux: fall back to montant_comptabilise if set.
        return (float) ($this->montant_comptabilise ?? 0);
    }

    /** Alias for PDF/views: total of the facture (sum of lignes). */
    public function getTotalComptabiliseAttribute(): float
    {
        return $this->total_facture;
    }

    /** Amount of this facture that is "comptabilisé" (official). Rest is internal. */
    public function getMontantComptabiliseAfficheAttribute(): float
    {
        return (float) ($this->montant_comptabilise ?? $this->total_facture);
    }

    /** Non comptabilisé (internal) = total facture - montant comptabilisé. */
    public function getMontantNonComptabiliseAttribute(): float
    {
        return max(0, $this->total_facture - $this->montant_comptabilise_affiche);
    }

    public function getTotalDhsAttribute(): float
    {
        if ($this->relationLoaded('travaux') && $this->travaux->isNotEmpty()) {
            return (float) $this->travaux->sum('prix_dhs');
        }

        return (float) ($this->montant_comptabilise ?? 0);
    }
}
