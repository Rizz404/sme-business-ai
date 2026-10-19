<?php

namespace App\Http\Requests\Api;

use App\Http\Middleware\ResolveBusiness;
use App\Modules\Booking\Validation\BookingRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Where, when and how many: the first thing the booking wizard checks.
 */
class QuoteBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return BookingRules::slot(ResolveBusiness::business($this));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return BookingRules::messages(ResolveBusiness::locale($this));
    }
}
