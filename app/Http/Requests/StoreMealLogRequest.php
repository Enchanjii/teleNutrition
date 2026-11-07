<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_id' => ['required', 'exists:foods,id'],
            'quantity' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'logged_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}