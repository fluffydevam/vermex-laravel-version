<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->client);
    }

    public function rules(): array
    {
        return [
            'client_name' => 'required|string|max:150',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'client_type' => 'required|in:Residential,Commercial',
            'email' => 'nullable|email|max:100|unique:clients,email,' . $this->client->id,
            'phone_number' => 'required|string|max:20',
            'street_address' => 'required|string|max:255',
            'barangay' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,archived',
        ];
    }
}
