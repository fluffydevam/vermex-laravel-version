<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use App\Enums\UserRole;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::BillingOfficer]);
    }

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
