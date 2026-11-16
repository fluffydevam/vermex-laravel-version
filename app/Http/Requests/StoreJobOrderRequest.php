<?php

namespace App\Http\Requests;

use App\Enums\JobOrderPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\JobOrder::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'technician_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'Field Technician')->where('status', 'active'),
            ],
            'service_type' => 'required|string|max:100',
            'scheduled_date' => 'required|date',
            'service_window' => 'required|string|max:50',
            'priority' => ['required', Rule::in(JobOrderPriority::values())],
            'comments' => 'nullable|string|max:2000',
            'area_findings' => 'nullable|string|max:4000',
        ];
    }

    public function messages(): array
    {
        return [
            'technician_id.exists' => 'The selected technician must be an active field technician.',
        ];
    }
}
