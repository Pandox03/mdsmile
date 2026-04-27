<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaisseMouvement extends Model
{
    protected $table = 'caisse_mouvements';

    public const TYPE_ENTREE = 'entree';
    public const TYPE_SORTIE = 'sortie';

    protected $fillable = [
        'type',
        'montant',
        'date_mouvement',
        'description',
        'facture_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_mouvement' => 'date',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_ENTREE => 'Entrée (ce que nous encaissons)',
            self::TYPE_SORTIE => 'Sortie (ce que nous dépensons)',
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function isEntree(): bool
    {
        return $this->type === self::TYPE_ENTREE;
    }

    public function isSortie(): bool
    {
        return $this->type === self::TYPE_SORTIE;
    }
}
