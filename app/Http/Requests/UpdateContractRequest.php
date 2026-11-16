<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->contract);
    }

    public function rules(): array
    {
        return [
            'contract_name' => 'required|string|max:150',
            'contract_status' => 'required|in:to_be_contracted,active,expiring_soon,expired,cancelled,archived',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'contract_value' => 'required|numeric|min:0',
        ];
    }
}
