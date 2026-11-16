<?php

namespace App\Models;

use App\Enums\InventoryCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'quantity_in_stock',
        'unit',
        'min_threshold',
        'unit_cost',
        'batch_number',
        'storage_location',
    ];

    protected $casts = [
        'category' => InventoryCategory::class,
        'quantity_in_stock' => 'decimal:2',
        'min_threshold' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'inventory_item_id');
    }

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn () => match (true) {
                (float) $this->quantity_in_stock <= 0 => 'Out of Stock',
                (float) $this->quantity_in_stock <= (float) $this->min_threshold => 'Low Stock',
                default => 'In Stock',
            },
        );
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= min_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_in_stock', '<=', 0);
    }
}
