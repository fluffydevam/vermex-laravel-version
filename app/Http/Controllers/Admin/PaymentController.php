<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesPayments;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    use HandlesPayments;

    protected function paymentRouteBase(): string
    {
        return 'admin.payments';
    }
}
