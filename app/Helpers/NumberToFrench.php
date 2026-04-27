<?php

namespace App\Helpers;

class NumberToFrench
{
    private const UNITS = [
        0 => 'zéro', 1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
        6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix', 11 => 'onze',
        12 => 'douze', 13 => 'treize', 14 => 'quatorze', 15 => 'quinze', 16 => 'seize',
        17 => 'dix-sept', 18 => 'dix-huit', 19 => 'dix-neuf',
    ];

    private const TENS = [
        2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante',
        7 => 'soixante', 8 => 'quatre-vingt', 9 => 'quatre-vingt',
    ];

    public static function toLetters(float $amount, string $devise = 'dirhams'): string
    {
        $int = (int) round($amount);
        $dec = (int) round(($amount - $int) * 100);
        $str = self::intToLetters($int);
        if ($int === 0) {
            $str = 'zéro';
        }
        $str = ucfirst($str) . ' ' . $devise;
        if ($dec > 0) {
            $str .= ' et ' . self::intToLetters($dec) . ' centimes';
        }
        return $str;
    }

    public static function intToLetters(int $n): string
    {
        if ($n < 0 || $n >= 1000000) {
            return (string) $n;
        }
        if ($n < 20) {
            return self::UNITS[$n];
        }
        if ($n < 100) {
            $t = (int) floor($n / 10);
            $u = $n % 10;
            if ($t === 7) {
                return $u === 0 ? 'soixante-dix' : 'soixante-' . self::UNITS[10 + $u];
            }
            if ($t === 9) {
                return $u === 0 ? 'quatre-vingt-dix' : 'quatre-vingt-' . self::UNITS[10 + $u];
            }
            if ($t === 8 && $u === 0) {
                return 'quatre-vingts';
            }
            if ($t === 8) {
                return 'quatre-vingt-' . self::UNITS[$u];
            }
            $base = self::TENS[$t];
            if ($u === 0) {
                return $base;
            }
            if ($t === 2 && $u === 1) {
                return $base . ' et un';
            }
            return $base . '-' . self::UNITS[$u];
        }
        if ($n < 1000) {
            $h = (int) floor($n / 100);
            $r = $n % 100;
            $cent = $h === 1 ? 'cent' : self::UNITS[$h] . ' cent';
            if ($r === 0) {
                return $h > 1 ? $cent . 's' : $cent;
            }
            return $cent . ' ' . self::intToLetters($r);
        }
        if ($n < 1000000) {
            $m = (int) floor($n / 1000);
            $r = $n % 1000;
            $mille = $m === 1 ? 'mille' : self::intToLetters($m) . ' mille';
            if ($r === 0) {
                return $mille;
            }
            return $mille . ' ' . self::intToLetters($r);
        }
        return (string) $n;
    }
}
