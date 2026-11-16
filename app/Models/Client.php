<?php

namespace App\Models;

use App\Enums\ClientType;
use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'street_address',
        'barangay',
        'city',
        'client_type',
        'status',
    ];

    protected $casts = [
        'client_type' => ClientType::class,
        'status' => ClientStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function ($client) {
            $client->client_name = trim($client->client_name);
        });
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->street_address}, {$this->barangay}, {$this->city}"),
        );
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function siteInspections(): HasMany
    {
        return $this->hasMany(SiteInspection::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Contract::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ClientStatus::Active->value);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', ClientStatus::Archived->value);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('client_type', $type);
    }
}
