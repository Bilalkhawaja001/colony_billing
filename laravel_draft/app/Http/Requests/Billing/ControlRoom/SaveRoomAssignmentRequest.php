<?php

namespace App\Http\Requests\Billing\ControlRoom;

use Illuminate\Foundation\Http\FormRequest;

class SaveRoomAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['nullable', 'string', 'max:255'],
            'room_no' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'rows' => ['prohibited'],
        ];
    }
}
