<?php

namespace App\Enums;

enum InspectionStatus: string
{
    case PendingVisit = 'Pending Visit';
    case InspectionComplete = 'Inspection Complete';
    case PestsFound = 'Inspection Complete (Pests Found)';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
