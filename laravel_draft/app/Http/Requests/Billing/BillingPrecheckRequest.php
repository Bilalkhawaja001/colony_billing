<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingPrecheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'string', 'max:20'],
            'scope' => ['nullable', 'string', 'max:50'],
            'dry_run' => ['nullable', 'boolean'],
        ];
    }
}
