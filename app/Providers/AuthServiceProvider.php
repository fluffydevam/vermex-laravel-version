<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\SiteInspection;
use App\Models\User;
use App\Policies\ClientPolicy;
use App\Policies\ContractPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\JobOrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SiteInspectionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Client::class => ClientPolicy::class,
        Contract::class => ContractPolicy::class,
        InventoryItem::class => InventoryItemPolicy::class,
        JobOrder::class => JobOrderPolicy::class,
        Payment::class => PaymentPolicy::class,
        SiteInspection::class => SiteInspectionPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-users', fn (User $user) => $user->canManageUsers());

        Gate::define('view-any-client', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::BillingOfficer,
        ], true));

        Gate::define('view-any-contract', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::BillingOfficer,
        ], true));

        Gate::define('view-any-job-order', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::FieldTechnician, UserRole::BillingOfficer,
        ], true));

        Gate::define('view-any-inspection', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::FieldTechnician,
        ], true));

        Gate::define('view-any-payment', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::BillingOfficer,
        ], true));

        Gate::define('view-any-inventory', fn (User $user) => in_array($user->role, [
            UserRole::Admin, UserRole::ChemicalCustodian, UserRole::FieldTechnician,
        ], true));
    }
}
