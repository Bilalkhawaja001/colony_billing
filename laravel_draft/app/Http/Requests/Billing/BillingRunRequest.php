<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Accept Flask-compatible MM-YYYY and UI payload YYYY-MM; service normalizes to MM-YYYY.
            'month_cycle' => ['required', 'string', 'regex:/^(\d{2}-\d{4}|\d{4}-\d{2})$/'],
            'run_key' => ['nullable', 'string'],
            'actor_user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
