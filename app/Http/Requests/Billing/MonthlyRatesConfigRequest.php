<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyRatesConfigRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/']];
    }

    protected function validationData(): array
    {
        return $this->query();
    }
}
