<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjust', $this->inventory_item);
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', Rule::in(['add', 'subtract', 'set'])],
            'adjust_amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'adjust_amount.min' => 'The adjustment amount must be greater than zero.',
        ];
    }
}
