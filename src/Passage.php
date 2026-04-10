<?php

declare(strict_types=1);

namespace Searoute;

final class Passage
{
    public const BABALMANDAB = 'babalmandab';
    public const BOSPORUS = 'bosporus';
    public const GIBRALTAR = 'gibraltar';
    public const SUEZ = 'suez';
    public const PANAMA = 'panama';
    public const ORMUZ = 'ormuz';
    public const NORTHWEST = 'northwest';
    public const MALACCA = 'malacca';
    public const SUNDA = 'sunda';
    public const CHILI = 'chili';
    public const SOUTH_AFRICA = 'south_africa';

    public static function validPassages(): array
    {
        return [
            self::BABALMANDAB,
            self::BOSPORUS,
            self::GIBRALTAR,
            self::SUEZ,
            self::PANAMA,
            self::ORMUZ,
            self::NORTHWEST,
            self::MALACCA,
            self::SUNDA,
            self::CHILI,
            self::SOUTH_AFRICA,
        ];
    }

    public static function filterValidPassages(array $values): array
    {
        $valid = array_flip(self::validPassages());
        $result = [];

        foreach ($values as $value) {
            if (isset($valid[$value])) {
                $result[$value] = true;
            }
        }

        return array_keys($result);
    }
}
