<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderMaterial extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job_order_id',
        'inventory_item_id',
        'quantity_used',
        'unit_used',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'logged_at' => 'datetime',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
