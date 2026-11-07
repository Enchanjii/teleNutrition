<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dietitian_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'dietitian'),
            ],
            'scheduled_at' => [
                'bail',
                'required',
                'date_format:Y-m-d\TH:i',
                'after:now',
                function (string $attribute, mixed $value, Closure $fail) {
                    $start = Carbon::createFromFormat('Y-m-d\TH:i', $value);

                    if ($start->minute % 15 !== 0) {
                        $fail('Please pick a time on the quarter hour.');
                    }

                    $minutes = $start->hour * 60 + $start->minute;
                    if ($minutes < 8 * 60 || $minutes > 16 * 60 + 30) {
                        $fail('Appointments are available from 8:00 AM to 4:30 PM.');
                    }
                },
            ],
        ];
    }
}