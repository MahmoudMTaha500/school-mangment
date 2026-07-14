<?php

namespace App\Support;

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
