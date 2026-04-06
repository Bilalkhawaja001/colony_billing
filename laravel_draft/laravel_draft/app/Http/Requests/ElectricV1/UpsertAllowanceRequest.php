<?php

namespace App\Http\Requests\ElectricV1;

use Illuminate\Foundation\Http\FormRequest;

class UpsertAllowanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'rows' => ['required','array','min:1'],
            'rows.*.unit_id' => ['required','string','max:64'],
            'rows.*.free_electric' => ['required','numeric','gte:0'],
            'rows.*.residence_type' => ['required','in:ROOM,HOUSE'],
            'rows.*.unit_name' => ['nullable','string','max:255'],
        ];
    }
}
