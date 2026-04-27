<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doc extends Model
{
    protected $table = 'docs';

    protected $fillable = [
        'numero_registration',
        'name',
        'phone',
        'email',
        'adresse',
    ];

    public function travaux(): HasMany
    {
        return $this->hasMany(Travail::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    public function situationEncaissements(): HasMany
    {
        return $this->hasMany(DocSituationEncaissement::class, 'doc_id');
    }

    public function docPrestationPrices(): HasMany
    {
        return $this->hasMany(DocPrestationPrice::class, 'doc_id');
    }

    /** Price for this client for the given prestation (override or default from Prestations). */
    public function getPriceForPrestation(Prestation $prestation): ?float
    {
        $override = $this->docPrestationPrices()->where('prestation_id', $prestation->id)->first();
        if ($override !== null) {
            return $override->price === null ? null : (float) $override->price;
        }
        return $prestation->price === null ? null : (float) $prestation->price;
    }

    /** Display string for price (e.g. "300 DH" or "Sur devis"). */
    public function getPriceDisplayForPrestation(Prestation $prestation): string
    {
        $price = $this->getPriceForPrestation($prestation);
        return $price === null ? 'Sur devis' : number_format($price, 0, ',', ' ') . ' DH';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->numero_registration ? ' (' . $this->numero_registration . ')' : '');
    }
}
