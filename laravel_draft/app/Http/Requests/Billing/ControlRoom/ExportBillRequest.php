<?php

namespace App\Http\Requests\Billing\ControlRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billing_month' => ['nullable', 'string', 'max:20'],
            'bill_type' => ['required', Rule::in(['electric_v1', 'water', 'school_van', 'all'])],
            'scope' => ['required', Rule::in(['all', 'colony', 'unit', 'room'])],
            'colony_type' => ['nullable', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:255'],
            'room_type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
