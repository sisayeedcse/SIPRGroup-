<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['active', 'approved', 'rejected'])],
            'quorum_required' => ['nullable', 'integer', 'min:1', 'max:500'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:date'],
        ];
    }
}
