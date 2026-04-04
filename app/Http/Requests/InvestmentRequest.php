<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role->value ?? $this->user()?->role;

        return in_array($role, ['admin', 'finance', 'secretary'], true);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sector' => ['nullable', 'string', 'max:255'],
            'partner' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'capital_deployed' => ['required', 'numeric', 'min:0'],
            'expected_return' => ['nullable', 'numeric', 'min:0'],
            'actual_return' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'completed', 'paused'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
