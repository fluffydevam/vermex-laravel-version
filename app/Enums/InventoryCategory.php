<?php

namespace App\Enums;

enum InventoryCategory: string
{
    case Chemical = 'Chemical';
    case DeviceTrap = 'Device/Trap';
    case Equipment = 'Equipment';
    case Ppe = 'PPE';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
