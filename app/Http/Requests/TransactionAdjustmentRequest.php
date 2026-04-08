<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role->value ?? $this->user()?->role;

        return in_array($role, ['admin', 'finance'], true);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['deposit', 'investment', 'profit', 'expense', 'fine'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
