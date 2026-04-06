<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class MonthTransitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/'],
            'to_state' => ['required', 'string', 'in:OPEN,INGEST,VALIDATION,APPROVAL,LOCKED'],
        ];
    }
}
