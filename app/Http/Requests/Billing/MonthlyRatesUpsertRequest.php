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
            'rates' => ['nullable', 'array', 'min:1'],
            'rates.*.utility_type' => ['required_with:rates', 'string', 'max:50'],
            'rates.*.rate' => ['required_with:rates', 'numeric', 'min:0'],
            'elec_rate' => ['nullable', 'numeric', 'min:0'],
            'water_general_rate' => ['nullable', 'numeric', 'min:0'],
            'water_drinking_rate' => ['nullable', 'numeric', 'min:0'],
            'school_van_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
