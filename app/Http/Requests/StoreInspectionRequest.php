<?php

namespace App\Http\Requests;

use App\Enums\InspectionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\SiteInspection::class);
    }

    public function rules(): array
    {
        return $this->inspectionRules();
    }

    protected function inspectionRules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'technician_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'Field Technician')->where('status', 'active'),
            ],
            'inspection_status' => ['required', Rule::in(InspectionStatus::values())],
            'inspection_date' => 'nullable|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'account_type' => 'nullable|string|max:100',
            'client_email' => 'nullable|email|max:255',
            'vermex_representative' => 'nullable|string|max:255',
            'client_representative' => 'nullable|string|max:255',
            'ilt_qty' => 'nullable|integer|min:0',
            'ilt_remarks' => 'nullable|string|max:255',
            'rat_cage_qty' => 'nullable|integer|min:0',
            'rat_cage_remarks' => 'nullable|string|max:255',
            'rat_bait_qty' => 'nullable|integer|min:0',
            'rat_bait_remarks' => 'nullable|string|max:255',
            'glue_trap_qty' => 'nullable|integer|min:0',
            'glue_trap_remarks' => 'nullable|string|max:255',
            'conditions' => 'nullable|array',
            'conditions.*.status' => 'nullable|in:,yes',
            'conditions.*.area' => 'nullable|string|max:255',
            'findings' => 'nullable|array',
            'findings.*.area' => 'nullable|string|max:255',
            'findings.*.findings' => 'nullable|string|max:255',
            'findings.*.action_taken' => 'nullable|string|max:255',
            'findings.*.remarks' => 'nullable|string|max:255',
        ];
    }
}
