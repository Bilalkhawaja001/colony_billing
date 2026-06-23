<?php

namespace App\Http\Requests\Billing\ControlRoom;

use Illuminate\Foundation\Http\FormRequest;

class SaveMeterReadingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meter_id' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['nullable', 'string', 'max:255'],
            'reading_date' => ['nullable', 'date'],
            'reading_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
