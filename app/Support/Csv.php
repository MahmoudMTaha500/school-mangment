<?php

namespace App\Support;

/**
 * Guards CSV exports against spreadsheet formula injection: a cell whose text
 * begins with a formula trigger (= + - @, tab, CR) is executed by Excel/Sheets
 * on open. Prefixing such values with a single quote neutralises them while
 * leaving the displayed text intact. Numbers pass through untouched.
 */
final class Csv
{
    private const TRIGGERS = ['=', '+', '-', '@', "\t", "\r"];

    public static function field(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], self::TRIGGERS, true) ? "'".$value : $value;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    public static function row(array $row): array
    {
        return array_map(static fn (mixed $value): mixed => self::field($value), $row);
    }
}
