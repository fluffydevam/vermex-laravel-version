<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->user);
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('users', 'username')->ignore($target->id),
            ],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($target->id),
            ],
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(UserRole::values())],
            'sector_region' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(UserStatus::values())],
        ];
    }
}
