<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role->value ?? $this->user()?->role;

        return in_array($role, ['admin', 'finance', 'secretary'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'done' => ['nullable', 'boolean'],
        ];
    }
}
