<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'birth_date', 'sex', 'weight_kg', 'height_cm', 'activity_level'])]
class PatientProfile extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }
}