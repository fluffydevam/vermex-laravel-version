<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'contract_name',
        'contract_status',
        'contract_start_date',
        'contract_end_date',
        'contract_value',
    ];

    protected $casts = [
        'contract_status' => ContractStatus::class,
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function siteInspections(): HasMany
    {
        return $this->hasMany(SiteInspection::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmount(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if (array_key_exists('payments_sum_amount_paid', $this->attributes)) {
                    return (float) $this->attributes['payments_sum_amount_paid'];
                }

                return (float) $this->payments()->sum('amount_paid');
            },
        );
    }

    public function finalBalance(): Attribute
    {
        return Attribute::make(
            get: fn (): float => max(0.0, (float) $this->contract_value - $this->paid_amount),
        );
    }

    public function scopeActive($query)
    {
        return $query->where('contract_status', ContractStatus::Active->value);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('contract_status', '!=', ContractStatus::Archived->value);
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('contract_status', ContractStatus::ExpiringSoon->value);
    }
}
