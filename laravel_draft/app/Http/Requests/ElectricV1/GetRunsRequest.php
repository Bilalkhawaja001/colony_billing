<?php

namespace App\Http\Requests\ElectricV1;

use Illuminate\Foundation\Http\FormRequest;

class GetRunsRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'cycle_start' => ['required','date_format:Y-m-d'],
            'cycle_end' => ['required','date_format:Y-m-d','after_or_equal:cycle_start'],
        ];
    }
}
