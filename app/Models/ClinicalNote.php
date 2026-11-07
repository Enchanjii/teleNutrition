<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'dietitian_id',
    'note_date',
    'assessment',
    'diagnosis_term',
    'etiology',
    'signs_symptoms',
    'intervention',
    'monitoring_evaluation',
])]
class ClinicalNote extends Model
{
    // Nutrition diagnosis labels. Verify the wording against eNCPT / your course material.
    public const DIAGNOSES = [
        'Excessive energy intake',
        'Inadequate energy intake',
        'Inadequate protein intake',
        'Excessive mineral intake (sodium)',
        'Inadequate fiber intake',
        'Inadequate oral intake',
        'Food- and nutrition-related knowledge deficit',
        'Undesirable food choices',
    ];

    protected function casts(): array
    {
        return [
            'note_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function dietitian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dietitian_id');
    }
}