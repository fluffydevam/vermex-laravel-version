<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogJobMaterialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->job_order);
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_used' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}
