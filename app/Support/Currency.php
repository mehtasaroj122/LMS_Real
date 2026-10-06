<?php

namespace App\Support;

/** Currency presentation only: never use formatted values for calculations or storage. */
final class Currency
{
    public const CODE = 'NPR';
    public const SYMBOL = 'रु';
    public const PREFIX = self::SYMBOL . ' ';

    public static function format($amount, int $decimals = 2): string
    {
        return self::PREFIX . number_format((float) $amount, $decimals);
    }

    /** Display legacy recorded currency without rewriting the underlying record. */
    public static function normalizeText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        return preg_replace('/(?:\x{20B9}\s*|\b(?:Rs\.?|INR)\s*|रु\s*)(?=[+-]?\d)/u', self::PREFIX, $text);
    }
}
