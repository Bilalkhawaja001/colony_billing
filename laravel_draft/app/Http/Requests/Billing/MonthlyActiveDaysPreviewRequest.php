<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyActiveDaysPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $month = trim((string) $this->input('billing_month_date', ''));
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month .= '-01';
        }

        $this->merge([
            'billing_month_date' => $month,
            'replace_existing' => filter_var($this->input('replace_existing', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    public function rules(): array
    {
        return [
            'billing_month_date' => ['required', 'date_format:Y-m-d'],
            'replace_existing' => ['nullable', 'boolean'],
            'upload_file' => ['required', 'file', 'mimes:csv,txt'],
        ];
    }
}
