<?php

namespace App\Enums;

enum JobOrderPriority: string
{
    case Standard = 'Standard';
    case High = 'High';
    case Urgent = 'Urgent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
