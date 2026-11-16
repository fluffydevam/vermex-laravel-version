<?php

namespace App\Policies;

use App\Models\User;
use App\Models\InventoryItem;
use App\Enums\UserRole;

class InventoryItemPolicy
{
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ChemicalCustodian, UserRole::FieldTechnician]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ChemicalCustodian]);
    }

    public function update(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ChemicalCustodian]);
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function adjust(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::ChemicalCustodian]);
    }
}
