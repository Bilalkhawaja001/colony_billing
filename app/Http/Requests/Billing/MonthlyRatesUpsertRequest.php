<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyRatesUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/'],
            'rates' => ['required', 'array', 'min:1'],
            'rates.*.utility_type' => ['required', 'string', 'max:50'],
            'rates.*.rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
