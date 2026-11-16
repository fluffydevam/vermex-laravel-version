<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contract;
use App\Enums\UserRole;

class ContractPolicy
{
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::BillingOfficer]);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function archive(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function restore(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
