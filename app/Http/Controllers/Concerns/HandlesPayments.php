<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

trait HandlesPayments
{
    /**
     * Route name prefix shared by the admin and billing ledgers.
     */
    abstract protected function paymentRouteBase(): string;

    public function index(Request $request, PaymentService $payments)
    {
        Gate::authorize('view-any-payment');

        $filters = $payments->filtersFrom($request);
        $contracts = $payments->eligibleContracts();

        return view('payments.index', [
            'payments' => $payments->paginate($filters),
            'metrics' => $payments->metrics($filters),
            'contracts' => $contracts,
            'contractOptions' => $contracts->mapWithKeys(fn ($contract) => [
                $contract->id => sprintf(
                    '[#CONTRACT-%04d] %s — %s (Bal: ₱%s)',
                    $contract->id,
                    $contract->client?->client_name ?? 'N/A',
                    $contract->contract_name,
                    number_format($contract->final_balance, 2),
                ),
            ])->all(),
            'contractBalances' => $contracts->mapWithKeys(fn ($contract) => [
                (string) $contract->id => round($contract->final_balance, 2),
            ])->all(),
            'filters' => $filters,
            'routeBase' => $this->paymentRouteBase(),
            'methodOptions' => $payments->methodOptions(),
            'typeOptions' => $payments->typeOptions(),
        ]);
    }

    public function store(StorePaymentRequest $request, PaymentService $payments)
    {
        Gate::authorize('create', Payment::class);

        $payment = $payments->record($request->validated(), $request->user());

        return redirect()
            ->route($this->paymentRouteBase())
            ->with('success', 'Payment of ₱' . number_format((float) $payment->amount_paid, 2) . ' recorded.');
    }
}
