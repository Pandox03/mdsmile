<?php

namespace App\Support;

class TypePaiement
{
    public const ESPECES = 'especes';
    public const CHEQUE = 'cheque';
    public const VIREMENT = 'virement';
    public const CARTE = 'carte';
    public const EFFET = 'effet';
    public const AUTRE = 'autre';

    public static function labels(): array
    {
        return [
            self::ESPECES => 'Espèces',
            self::CHEQUE => 'Chèque',
            self::VIREMENT => 'Virement',
            self::CARTE => 'Carte bancaire',
            self::EFFET => 'Effet',
            self::AUTRE => 'Autre',
        ];
    }

    public static function label(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return self::labels()[$value] ?? $value;
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }
}
