<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;

class TransportFuelEntryUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1', 'exists:transport_fuel_entries,id'],
            'month_cycle' => ['required', 'string', 'regex:/^(\d{2}-\d{4}|\d{4}-\d{2})$/'],
            'entry_date' => ['required', 'date'],
            'vehicle_id' => ['required', 'integer', 'min:1', 'exists:transport_vehicles,id'],
            'fuel_liters' => ['required', 'numeric', 'gt:0'],
            'fuel_price' => ['required', 'numeric', 'min:0'],
            'slip_ref' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
