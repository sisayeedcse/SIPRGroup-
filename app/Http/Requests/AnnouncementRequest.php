<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role->value ?? $this->user()?->role;

        return in_array($role, ['admin', 'secretary'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'pinned' => ['nullable', 'boolean'],
        ];
    }
}
