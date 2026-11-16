<?php

namespace App\Http\Requests;

use App\Enums\InventoryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->inventory_item);
    }

    public function rules(): array
    {
        return [
            'item_name' => [
                'required', 'string', 'max:255',
                Rule::unique('inventory_items', 'item_name')->ignore($this->inventory_item->id),
            ],
            'category' => ['required', Rule::in(InventoryCategory::values())],
            'unit' => 'required|string|max:50',
            'min_threshold' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'storage_location' => 'nullable|string|max:100',
        ];
    }
}
