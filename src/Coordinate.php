<?php

declare(strict_types=1);

namespace Searoute;

final class Coordinate
{
    public static function key(array $point): string
    {
        return self::normalizeNumber($point[0]) . ',' . self::normalizeNumber($point[1]);
    }

    public static function fromKey(string $key): array
    {
        [$lon, $lat] = array_map('floatval', explode(',', $key, 2));

        return [$lon, $lat];
    }

    private static function normalizeNumber(float|int|string $value): string
    {
        $number = (float) $value;
        $normalized = rtrim(rtrim(sprintf('%.12F', $number), '0'), '.');

        return $normalized === '-0' ? '0' : $normalized;
    }
}
