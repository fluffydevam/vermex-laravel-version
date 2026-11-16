<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'Cash';
    case BankTransfer = 'Bank Transfer';
    case Check = 'Check';
    case Gcash = 'GCash';
    case CreditCard = 'Credit Card';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
