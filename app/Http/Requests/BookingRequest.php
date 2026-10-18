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
            'recurrence_rule' => ['nullable', Rule::in(['weekly'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if ($startTime && $endTime) {
                $startParts = explode(':', (string) $startTime);
                $endParts = explode(':', (string) $endTime);

                if (count($startParts) === 2 && count($endParts) === 2) {
                    $sM = (int) $startParts[1];
                    $eM = (int) $endParts[1];

                    if (! in_array($sM, [0, 30], true) || ! in_array($eM, [0, 30], true)) {
                        $validator->errors()->add('start_time', 'Jadwal slot harus dalam kelipatan 30 menit (contoh: 08:00, 08:30).');
                    }

                    $durationMinutes = ((int) $endParts[0] * 60 + $eM) - ((int) $startParts[0] * 60 + $sM);
                    if ($durationMinutes < 60) {
                        $validator->errors()->add('end_time', 'Durasi booking minimal 1 jam.');
                    } elseif ($durationMinutes > 240) {
                        $validator->errors()->add('end_time', 'Durasi booking maksimal 4 jam.');
                    }
                }
            }

            if ($this->input('booking_date') === today()->toDateString()) {
                if ($startTime && $startTime <= now()->format('H:i')) {
                    $validator->errors()->add('start_time', 'Waktu mulai harus lebih dari waktu saat ini.');
                }
            }
        });
    }
}
