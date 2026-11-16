<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteInspection extends Model
{
    use HasFactory;

    /**
     * Standard site-condition checklist carried over from the legacy inspection sheet.
     */
    public const CONDITION_LABELS = [
        'Prolonged Open Door - Entry Point of Pests',
        'Open Garbage Bin - Attracts Rodents & Flies',
        'Litter/Dirt on Floor - Attracts Pests',
        'Food Debris on Floor - Attracts Pests',
        'Gaps on Door/Window - Entry Point',
        'Hole on Wall / Ceiling - Entry Point',
        'Poor Storage Practices - Harborage',
        'Clogged Drain / Stagnant Water',
        'Floor Drain - No Cover',
    ];

    protected $fillable = [
        'contract_id',
        'client_id',
        'technician_id',
        'client_name',
        'client_email',
        'service_address',
        'account_type',
        'inspection_status',
        'inspection_date',
        'time_in',
        'time_out',
        'ilt_qty',
        'ilt_remarks',
        'rat_cage_qty',
        'rat_cage_remarks',
        'rat_bait_qty',
        'rat_bait_remarks',
        'glue_trap_qty',
        'glue_trap_remarks',
        'vermex_representative',
        'client_representative',
        'conditions_json',
        'findings_json',
        'pests_found',
    ];

    protected $casts = [
        'inspection_status' => InspectionStatus::class,
        'inspection_date' => 'date',
        'ilt_qty' => 'integer',
        'rat_cage_qty' => 'integer',
        'rat_bait_qty' => 'integer',
        'glue_trap_qty' => 'integer',
        'conditions_json' => 'array',
        'findings_json' => 'array',
        'pests_found' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function scopePending($query)
    {
        return $query->where('inspection_status', InspectionStatus::PendingVisit->value);
    }

    public function scopeCompleted($query)
    {
        return $query->where('inspection_status', 'like', 'Inspection Complete%');
    }
}
