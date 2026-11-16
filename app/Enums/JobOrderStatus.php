<?php

namespace App\Enums;

enum JobOrderStatus: string
{
    case Scheduled = 'Scheduled';
    case EnRoute = 'En route';
    case OnSite = 'On site';
    case Completed = 'Completed';
    case Delayed = 'Delayed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
