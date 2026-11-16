<?php

namespace App\Http\Requests;

use App\Enums\JobOrderPriority;
use App\Enums\JobOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->job_order);
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
            'route_status' => ['required', Rule::in(JobOrderStatus::values())],
            'comments' => 'nullable|string|max:2000',
            'area_findings' => 'nullable|string|max:4000',
        ];
    }
}
