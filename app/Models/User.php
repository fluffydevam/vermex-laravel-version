<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'role',
        'sector_region',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'status' => UserStatus::class,
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isBillingOfficer(): bool
    {
        return $this->role === UserRole::BillingOfficer;
    }

    public function isFieldTechnician(): bool
    {
        return $this->role === UserRole::FieldTechnician;
    }

    public function isChemicalCustodian(): bool
    {
        return $this->role === UserRole::ChemicalCustodian;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Landing route for this user's role.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            UserRole::Admin => 'admin.dashboard',
            UserRole::BillingOfficer => 'billing.dashboard',
            UserRole::FieldTechnician => 'field.dashboard',
            UserRole::ChemicalCustodian => 'custodian.dashboard',
            default => 'login',
        };
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class, 'performed_by');
    }

    public function jobOrdersAsTechnician()
    {
        return $this->hasMany(JobOrder::class, 'technician_id');
    }

    public function siteInspectionsAsTechnician()
    {
        return $this->hasMany(SiteInspection::class, 'technician_id');
    }
}
