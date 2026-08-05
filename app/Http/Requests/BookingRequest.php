<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_id' => ['required', 'exists:courts,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'checked_in', 'finished', 'cancelled', 'expired'])],
            'payment_status' => ['sometimes', Rule::in(['pending', 'paid', 'failed', 'expired', 'refunded'])],
            'recurrence_rule' => ['nullable', Rule::in(['weekly'])],
        ];
    }
}
