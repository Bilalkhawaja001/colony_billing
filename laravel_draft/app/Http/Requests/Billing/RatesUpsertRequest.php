<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class RatesUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'string', 'regex:/^(\d{2}-\d{4}|\d{4}-\d{2})$/'],
            'elec_rate' => ['required', 'numeric', 'min:0'],
            'water_general_rate' => ['required', 'numeric', 'min:0'],
            'water_drinking_rate' => ['required', 'numeric', 'min:0'],
            'school_van_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
