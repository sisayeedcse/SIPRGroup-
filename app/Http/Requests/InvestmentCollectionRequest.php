<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role->value ?? $this->user()?->role;

        return in_array($role, ['admin', 'finance', 'secretary'], true);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'kg' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'sold_kg' => ['nullable', 'numeric', 'min:0'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'profit' => ['nullable', 'numeric'],
        ];
    }
}
