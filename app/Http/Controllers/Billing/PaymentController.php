<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Concerns\HandlesPayments;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    use HandlesPayments;

    protected function paymentRouteBase(): string
    {
        return 'billing.payments';
    }
}
