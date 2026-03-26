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
            'month_cycle' => ['required', 'string'],
            'run_key' => ['nullable', 'string'],
            'actor_user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
