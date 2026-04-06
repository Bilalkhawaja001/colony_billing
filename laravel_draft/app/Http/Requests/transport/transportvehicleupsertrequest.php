<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportVehicleUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'id' => ['nullable', 'integer', 'min:1', 'exists:transport_vehicles,id'],
            'vehicle_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('transport_vehicles', 'vehicle_code')->ignore($id),
            ],
            'vehicle_name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
