<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;
use App\Enums\UserRole;

class PaymentPolicy
{
    public function view(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::BillingOfficer]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::BillingOfficer]);
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
