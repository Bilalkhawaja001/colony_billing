<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'run_id' => ['nullable', 'integer', 'min:1'],
            'actor_user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
