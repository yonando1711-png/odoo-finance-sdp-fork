<?php

namespace App\Helpers;

class Terbilang
{
    private static $words = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
        'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
    ];

    public static function convert(float $number): string
    {
        $number = abs(floor($number));

        if ($number < 12) {
            return self::$words[$number];
        }
        if ($number < 20) {
            return self::$words[$number - 10] . ' Belas';
        }
        if ($number < 100) {
            return self::$words[(int)($number / 10)] . ' Puluh ' . self::$words[$number % 10];
        }
        if ($number < 200) {
            return 'Seratus ' . self::convert($number - 100);
        }
        if ($number < 1000) {
            return self::$words[(int)($number / 100)] . ' Ratus ' . self::convert($number % 100);
        }
        if ($number < 2000) {
            return 'Seribu ' . self::convert($number - 1000);
        }
        if ($number < 1000000) {
            return self::convert((int)($number / 1000)) . ' Ribu ' . self::convert($number % 1000);
        }
        if ($number < 1000000000) {
            return self::convert((int)($number / 1000000)) . ' Juta ' . self::convert($number % 1000000);
        }
        if ($number < 1000000000000) {
            return self::convert((int)($number / 1000000000)) . ' Miliar ' . self::convert($number % 1000000000);
        }
        if ($number < 1000000000000000) {
            return self::convert((int)($number / 1000000000000)) . ' Triliun ' . self::convert($number % 1000000000000);
        }

        return '';
    }
}
