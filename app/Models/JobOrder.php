<?php

namespace App\Models;

use App\Enums\JobOrderStatus;
use App\Enums\JobOrderPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'contract_id',
        'technician_id',
        'client_name',
        'service_address',
        'service_type',
        'scheduled_date',
        'service_window',
        'priority',
        'route_status',
        'time_in',
        'time_out',
        'comments',
        'area_findings',
    ];

    protected $casts = [
        'priority' => JobOrderPriority::class,
        'route_status' => JobOrderStatus::class,
        'scheduled_date' => 'date',
        'area_findings' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(JobOrderMaterial::class);
    }

    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'job_order_materials')
            ->withPivot(['quantity_used', 'unit_used', 'logged_at']);
    }

    public function scopeScheduled($query)
    {
        return $query->where('route_status', JobOrderStatus::Scheduled->value);
    }

    public function scopeCompleted($query)
    {
        return $query->where('route_status', JobOrderStatus::Completed->value);
    }
}
