<?php

namespace App\Http\Requests;

use App\Enums\InventoryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InventoryItem::class);
    }

    public function rules(): array
    {
        return [
            'item_name' => 'required|string|max:255|unique:inventory_items,item_name',
            'category' => ['required', Rule::in(InventoryCategory::values())],
            'quantity_in_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'min_threshold' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'storage_location' => 'nullable|string|max:100',
        ];
    }
}
