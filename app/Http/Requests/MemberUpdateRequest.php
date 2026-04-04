<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->role->value ?? $this->user()?->role) === 'admin';
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'finance', 'secretary', 'member'])],
            'status' => ['required', Rule::in(['active', 'pending', 'removed'])],
            'locked' => ['required', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
