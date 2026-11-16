<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SiteInspection;
use App\Enums\UserRole;

class SiteInspectionPolicy
{
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::FieldTechnician]);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, SiteInspection $inspection): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::FieldTechnician) {
            return $inspection->technician_id === $user->id;
        }
        return false;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
