<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class ImportsIngestPreviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/'],
            'rows_received' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
