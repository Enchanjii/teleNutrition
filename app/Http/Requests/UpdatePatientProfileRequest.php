<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'sex' => ['required', 'in:male,female'],
            'weight_kg' => ['required', 'numeric', 'min:20', 'max:300'],
            'height_cm' => ['required', 'numeric', 'min:80', 'max:250'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,active,very_active'],
        ];
    }
}