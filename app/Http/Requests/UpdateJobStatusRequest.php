<?php

namespace App\Http\Requests;

use App\Enums\JobOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->job_order);
    }

    public function rules(): array
    {
        return [
            'route_status' => ['required', Rule::in(JobOrderStatus::values())],
            'comments' => 'nullable|string|max:2000',
        ];
    }
}
