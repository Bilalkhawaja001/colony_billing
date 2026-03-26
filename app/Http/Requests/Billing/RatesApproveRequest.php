<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class RatesApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'string'],
            'actor_user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
