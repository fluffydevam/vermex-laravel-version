<?php

namespace App\Http\Requests;

use App\Enums\ContractStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payment::class);
    }

    public function rules(): array
    {
        return [
            'contract_id' => [
                'required',
                'integer',
                Rule::exists('contracts', 'id')->whereNot('contract_status', ContractStatus::Archived->value),
            ],
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_type' => ['required', Rule::in(PaymentType::values())],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:1000',
            'payment_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'contract_id.exists' => 'Select an active contract that accepts payments.',
        ];
    }
}
