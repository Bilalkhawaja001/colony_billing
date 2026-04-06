<?php

namespace App\Http\Requests\ElectricV1;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'rows' => ['required','array','min:1'],
            'rows.*.cycle_start_date' => ['required','date_format:Y-m-d'],
            'rows.*.cycle_end_date' => ['required','date_format:Y-m-d'],
            'rows.*.company_id' => ['required','string','max:64'],
            'rows.*.attendance_days' => ['required','numeric','gte:0'],
        ];
    }
}
