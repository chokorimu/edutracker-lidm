<?php

namespace App\Services;

class BebanCalculator
{
    public const LIGHT = 'ringan';
    public const NORMAL = 'normal';
    public const HEAVY = 'berat';
    public const OVERLOAD = 'overload';

    public static function forCount(int $count): string
    {
        return match (true) {
            $count <= 1 => self::LIGHT,
            $count === 2 => self::NORMAL,
            $count === 3 => self::HEAVY,
            default => self::OVERLOAD,
        };
    }
}
