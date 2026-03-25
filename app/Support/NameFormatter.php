<?php

namespace App\Support;

final class NameFormatter
{
    /**
     * Convert a person-name input into proper case while preserving
     * spacing, apostrophes, and hyphenated tokens.
     */
    public static function properCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed == '') {
            return null;
        }

        // Collapse repeated spaces while preserving single spacing between words.
        $normalized = preg_replace('/\s+/u', ' ', $trimmed) ?? $trimmed;
        $tokens = explode(' ', $normalized);
        $formatted = array_map(function (string $token): string {
            return self::formatToken($token);
        }, $tokens);

        return implode(' ', $formatted);
    }

    /**
     * Normalize suffix values, e.g. jr -> Jr., iii -> III.
     */
    public static function formatSuffix(?string $suffix): ?string
    {
        $value = self::properCase($suffix);
        if ($value === null) {
            return null;
        }

        $upper = strtoupper(str_replace('.', '', $value));

        return match ($upper) {
            'JR' => 'Jr.',
            'SR' => 'Sr.',
            'I', 'II', 'III', 'IV', 'V' => $upper,
            default => $value,
        };
    }

    private static function formatToken(string $token): string
    {
        $parts = preg_split("/([-'])/u", $token, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return self::titleWord($token);
        }

        $result = '';
        foreach ($parts as $part) {
            if ($part === '-' || $part === "'") {
                $result .= $part;
                continue;
            }

            $result .= self::titleWord($part);
        }

        return $result;
    }

    private static function titleWord(string $word): string
    {
        if ($word === '') {
            return '';
        }

        $upper = strtoupper($word);
        if (in_array($upper, ['I', 'II', 'III', 'IV', 'V'], true)) {
            return $upper;
        }

        return mb_convert_case(mb_strtolower($word), MB_CASE_TITLE, 'UTF-8');
    }
}

