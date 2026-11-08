<?php

namespace App\Http\Requests;

use App\Models\ClinicalNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_date' => ['required', 'date', 'before_or_equal:today'],
            'assessment' => ['required', 'string', 'max:2000'],
            'diagnosis_term' => ['required', Rule::in(ClinicalNote::DIAGNOSES)],
            'etiology' => ['required', 'string', 'max:500'],
            'signs_symptoms' => ['required', 'string', 'max:1000'],
            'intervention' => ['required', 'string', 'max:2000'],
            'monitoring_evaluation' => ['required', 'string', 'max:2000'],
        ];
    }
}