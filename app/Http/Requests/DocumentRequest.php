<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
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
            'category' => ['required', Rule::in(['meeting-notes', 'financial-report', 'legal', 'research', 'photo', 'other'])],
            'url' => ['nullable', 'url', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:10240', 'required_without:url'],
        ];
    }
}
