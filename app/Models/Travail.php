<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Travail extends Model
{
    protected $table = 'travaux';

    public const PAIEMENT_NON_PAYE = 'non_paye';
    public const PAIEMENT_PARTIEL = 'partiel';
    public const PAIEMENT_PAYE = 'paye';

    protected $fillable = [
        'doc_id',
        'prestation_id',
        'dentiste',
        'patient',
        'numero_fiche',
        'patient_age',
        'type_travail',
        'date_entree',
        'date_livraison',
        'date_essiage',
        'prix_dhs',
        'montant_comptabilise',
        'prix_comptabilise',
        'statut',
        'date_fait',
        'paiement_statut',
        'type_paiement',
    ];

    protected $casts = [
        'date_entree' => 'date',
        'date_livraison' => 'date',
        'date_essiage' => 'date',
        'date_fait' => 'date',
        'prix_dhs' => 'decimal:2',
        'montant_comptabilise' => 'decimal:2',
        'prix_comptabilise' => 'decimal:2',
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_EN_ESSAIAGE = 'en_essaiage';
    public const STATUT_TERMINE = 'termine';
    public const STATUT_LIVRER = 'livrer';
    public const STATUT_ANNULE = 'annule';
    public const STATUT_A_REFAIRE = 'a_refaire';

    /** Statuts qui font entrer le travail dans une situation, à la date du jour où il est marqué ainsi. */
    public static function statutsFaits(): array
    {
        return [self::STATUT_TERMINE, self::STATUT_LIVRER];
    }

    /**
     * Date de situation : le jour où le travail passe Terminé ou Livré.
     * Conservée tant qu'il reste dans ces statuts ; effacée s'il redevient en cours.
     */
    public static function dateFaitForStatutChange(string $previousStatut, mixed $previousDateFait, string $newStatut): ?string
    {
        $done = self::statutsFaits();
        if (! in_array($newStatut, $done, true)) {
            return null;
        }
        if (in_array($previousStatut, $done, true) && $previousDateFait) {
            if ($previousDateFait instanceof \DateTimeInterface) {
                return $previousDateFait->format('Y-m-d');
            }

            return (string) $previousDateFait;
        }

        return now()->toDateString();
    }

    public static function statutLabels(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_EN_ESSAIAGE => 'En essaiage',
            self::STATUT_TERMINE => 'Terminé',
            self::STATUT_LIVRER => 'Livrer',
            self::STATUT_A_REFAIRE => 'À refaire',
            self::STATUT_ANNULE => 'Annulé',
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
        return self::paiementLabels()[$this->paiement_statut ?? self::PAIEMENT_NON_PAYE]
            ?? ($this->paiement_statut ?? self::PAIEMENT_NON_PAYE);
    }

    public function isPaiementEffectue(): bool
    {
        return in_array($this->paiement_statut, [self::PAIEMENT_PARTIEL, self::PAIEMENT_PAYE], true);
    }

    /**
     * Référence affichée (ex. TR-00001).
     */
    public function getReferenceAttribute(): string
    {
        return 'TR-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Cap comptabilisé : montant du travail qui peut être facturé (officiel). Si null = tout prix_dhs.
     */
    public function getMontantComptabiliseCapAttribute(): float
    {
        return (float) ($this->montant_comptabilise ?? $this->prix_dhs);
    }

    /**
     * Part non comptabilisée (interne) = prix_dhs - cap.
     */
    public function getMontantNonComptabiliseAttribute(): float
    {
        return max(0, (float) $this->prix_dhs - $this->montant_comptabilise_cap);
    }

    /**
     * Prix actuel (officiel) = cap comptabilisé (montant affiché et facturable).
     */
    public function getPrixActuelAttribute(): float
    {
        return $this->montant_comptabilise_cap;
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    /** Type de travail affiché (prestation ou legacy type_travail). */
    public function getTypeTravailDisplayAttribute(): string
    {
        return $this->prestation?->name ?? $this->type_travail ?? '—';
    }

    public function teeth(): HasMany
    {
        return $this->hasMany(TravailTooth::class);
    }

    public function factures(): BelongsToMany
    {
        return $this->belongsToMany(Facture::class, 'facture_travail')->withPivot('prix_comptabilise')->withTimestamps();
    }

    /**
     * Somme des montants déjà rattachés à des factures (tous statuts — plafond facturable restant).
     */
    public function montantAlloueFactures(): float
    {
        return (float) DB::table('facture_travail')->where('travail_id', $this->id)->sum('prix_comptabilise');
    }

    /**
     * Somme des montants déjà liés à des factures pour ce travail.
     */
    public function montantPayeFactures(): float
    {
        return (float) DB::table('facture_travail')
            ->join('factures', 'factures.id', '=', 'facture_travail.facture_id')
            ->where('facture_travail.travail_id', $this->id)
            ->sum('facture_travail.prix_comptabilise');
    }

    /**
     * Reste disponible pour de nouvelles lignes de facture.
     */
    public function getMontantRestantAFacturerAttribute(): float
    {
        return max(0, round($this->montant_comptabilise_cap - $this->montantAlloueFactures(), 2));
    }

    /**
     * Reste après déduction des montants déjà facturés.
     */
    public function getMontantResteSituationAttribute(): float
    {
        return max(0, round($this->montant_comptabilise_cap - $this->montantPayeFactures(), 2));
    }

    /**
     * @param  array<int>  $travailIds
     * @return array<int, float> travail_id => montant facturé
     */
    public static function montantsPayesParTravailIds(array $travailIds): array
    {
        if ($travailIds === []) {
            return [];
        }
        $rows = DB::table('facture_travail')
            ->join('factures', 'factures.id', '=', 'facture_travail.facture_id')
            ->whereIn('facture_travail.travail_id', $travailIds)
            ->groupBy('facture_travail.travail_id')
            ->selectRaw('facture_travail.travail_id as tid, SUM(facture_travail.prix_comptabilise) as total')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->tid] = (float) $row->total;
        }

        return $map;
    }
}
