<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User as UserModel;
use App\Enums\UserRole;
use App\Enums\UserStatus;

class UserPolicy
{
    public function view(User $user): bool
    {
        return $user->role === UserRole::Admin;
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

    public function toggleStatus(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function resetPassword(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function disableSelf(User $user, UserModel $target): bool
    {
        return $user->id !== $target->id;
    }

    public function disableLastAdmin(User $user, UserModel $target): bool
    {
        if ($user->role !== UserRole::Admin) {
            return false;
        }
        if ($target->role !== UserRole::Admin) {
            return true;
        }
        $adminCount = UserModel::where('role', UserRole::Admin->value)->where('status', UserStatus::Active->value)->count();
        return $adminCount > 1 || $user->id === $target->id;
    }
}
