<?php

namespace App\Http\Requests;

class UpdateInspectionRequest extends StoreInspectionRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->inspection);
    }
}
