<?php

use Illuminate\Support\Facades\Route;

// Role-aware navigation: label, route, icon, roles (array of roles allowed)
// Roles: Admin, Billing Officer, Field Technician, Chemical Custodian
return [
    'Admin' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Clients & Contracts', 'route' => 'admin.clients', 'icon' => 'users'],
        ['label' => 'Dispatch', 'route' => 'admin.job-orders', 'icon' => 'calendar'],
        ['label' => 'Inspections', 'route' => 'admin.inspections', 'icon' => 'clipboard-check'],
        ['label' => 'Payments', 'route' => 'admin.payments', 'icon' => 'credit-card'],
        ['label' => 'Inventory', 'route' => 'admin.inventory', 'icon' => 'flask-conical'],
        ['label' => 'Reports', 'route' => 'admin.reports', 'icon' => 'bar-chart-3'],
        ['label' => 'Users', 'route' => 'admin.users', 'icon' => 'shield-check'],
    ],
    'Billing Officer' => [
        ['label' => 'Payments', 'route' => 'billing.payments', 'icon' => 'credit-card'],
        ['label' => 'Client Accounts', 'route' => 'billing.accounts', 'icon' => 'users'],
        ['label' => 'Financial Reports', 'route' => 'billing.reports', 'icon' => 'bar-chart-3'],
    ],
    'Field Technician' => [
        ['label' => 'Dashboard', 'route' => 'field.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Job Orders', 'route' => 'field.job-orders', 'icon' => 'calendar'],
        ['label' => 'Inspections', 'route' => 'field.inspections', 'icon' => 'clipboard-check'],
    ],
    'Chemical Custodian' => [
        ['label' => 'Dashboard', 'route' => 'custodian.dashboard', 'icon' => 'package'],
        ['label' => 'Inventory', 'route' => 'custodian.inventory', 'icon' => 'layers'],
    ],
];
