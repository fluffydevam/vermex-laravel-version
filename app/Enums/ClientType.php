<?php

namespace App\Enums;

enum ClientType: string
{
    case Residential = 'Residential';
    case Commercial = 'Commercial';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
