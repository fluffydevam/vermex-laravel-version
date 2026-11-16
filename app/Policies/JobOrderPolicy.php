<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobOrder;
use App\Enums\UserRole;

class JobOrderPolicy
{
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::FieldTechnician, UserRole::BillingOfficer]);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, JobOrder $jobOrder): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::FieldTechnician) {
            return $jobOrder->technician_id === $user->id;
        }
        return false;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
