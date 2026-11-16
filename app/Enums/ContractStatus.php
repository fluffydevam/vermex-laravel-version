<?php

namespace App\Enums;

enum ContractStatus: string
{
    case ToBeContracted = 'to_be_contracted';
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
