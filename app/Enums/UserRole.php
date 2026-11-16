<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case BillingOfficer = 'Billing Officer';
    case FieldTechnician = 'Field Technician';
    case ChemicalCustodian = 'Chemical Custodian';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
