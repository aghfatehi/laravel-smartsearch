<?php

namespace SmartSearch\Support;

class ArabicNormalizer
{
    private static array $charMap = [
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
        'ة' => 'ه',
        'ى' => 'ي',
        'ؤ' => 'و', 'ئ' => 'ي',
        'ك' => 'ك',
    ];

    private static array $diacritics = [
        'َ', 'ُ', 'ِ', 'ّ', 'ْ', 'ً', 'ٌ', 'ٍ',
    ];

    public static function normalize(string $text): string
    {
        $text = str_replace(self::$diacritics, '', $text);
        $text = strtr($text, self::$charMap);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    public static function normalizeArray(array $texts): array
    {
        return array_map([self::class, 'normalize'], $texts);
    }
}
