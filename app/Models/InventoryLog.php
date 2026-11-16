<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'performed_by',
        'job_order_id',
        'site_inspection_id',
        'action_type',
        'quantity_changed',
        'remarks',
    ];

    protected $casts = [
        'quantity_changed' => 'decimal:2',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function siteInspection(): BelongsTo
    {
        return $this->belongsTo(SiteInspection::class);
    }

    public function isAddition(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->quantity_changed > 0,
        );
    }

    public function isDeduction(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->quantity_changed < 0,
        );
    }
}
