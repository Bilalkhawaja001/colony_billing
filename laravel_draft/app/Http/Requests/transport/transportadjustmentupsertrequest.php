<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportAdjustmentUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1', 'exists:transport_adjustments,id'],
            'month_cycle' => ['required', 'string', 'regex:/^(\d{2}-\d{4}|\d{4}-\d{2})$/'],
            'vehicle_id' => ['nullable', 'integer', 'min:1', 'exists:transport_vehicles,id'],
            'direction' => ['required', Rule::in(['plus', 'minus'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
