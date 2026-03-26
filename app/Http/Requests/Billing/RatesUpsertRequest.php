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
            'month_cycle' => ['required', 'string'],
            'elec_rate' => ['required', 'numeric'],
            'water_general_rate' => ['required', 'numeric'],
            'water_drinking_rate' => ['required', 'numeric'],
            'school_van_rate' => ['required', 'numeric'],
        ];
    }
}
