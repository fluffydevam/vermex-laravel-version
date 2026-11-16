<?php

namespace App\Enums;

enum PaymentType: string
{
    case Downpayment = 'Downpayment';
    case BalanceSettlement = 'Balance Settlement';
    case FullPayment = 'Full Payment';
    case Adjustment = 'Adjustment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
