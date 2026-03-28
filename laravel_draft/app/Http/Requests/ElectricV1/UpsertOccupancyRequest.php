<?php

namespace App\Http\Requests\ElectricV1;

use Illuminate\Foundation\Http\FormRequest;

class UpsertOccupancyRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'rows' => ['required','array','min:1'],
            'rows.*.company_id' => ['required','string','max:64'],
            'rows.*.unit_id' => ['required','string','max:64'],
            'rows.*.room_id' => ['required','string','max:64'],
            'rows.*.from_date' => ['required','date_format:Y-m-d'],
            'rows.*.to_date' => ['required','date_format:Y-m-d','after_or_equal:rows.*.from_date'],
        ];
    }
}
