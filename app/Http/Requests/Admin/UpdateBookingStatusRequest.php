<?php

namespace App\Http\Requests\Admin;

use App\Modules\Booking\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * The business team confirms or cancels a pending booking; it never goes back
 * to pending.
 */
class UpdateBookingStatusRequest extends FormRequest
{
    /**
     * A record of another business is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('booking');

        if ($record) {
            Gate::authorize('update', $record);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([BookingStatus::Confirmed->value, BookingStatus::Cancelled->value])],
        ];
    }
}
