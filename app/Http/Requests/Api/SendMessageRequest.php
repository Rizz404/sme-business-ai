<?php

namespace App\Http\Requests\Api;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One guest message plus where in the UI the guest is when they send it.
 */
class SendMessageRequest extends FormRequest
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
        return [
            'guest_token' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:2000'],
            'scene' => ['nullable', Rule::in(Conversation::SCENES)],
            'selected_catalog_item' => ['nullable', 'string', 'max:120'],
            'selected_service_area' => ['nullable', 'string', 'max:120'],
            'selected_facility' => ['nullable', 'integer', 'min:1'],
            'booking' => ['nullable', 'array'],
            'booking.booking_date' => ['nullable', 'date_format:Y-m-d'],
            'booking.time_slot' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'booking.attendees' => ['nullable', 'integer', 'min:1', 'max:30'],
            'booking.purpose' => ['nullable', 'string', 'max:30'],
            'booking.service_area_slug' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Where the guest is in the UI, as the assistant's conversation remembers it.
     *
     * @return array{scene?: ?string, selected_catalog_item?: ?string, selected_service_area?: ?string, selected_facility?: ?int, booking?: array<string, mixed>}
     */
    public function uiContext(): array
    {
        $data = $this->validated();

        return [
            'scene' => $data['scene'] ?? null,
            'selected_catalog_item' => $data['selected_catalog_item'] ?? null,
            'selected_facility' => $data['selected_facility'] ?? null,
            'selected_service_area' => $data['selected_service_area'] ?? ($data['booking']['service_area_slug'] ?? null),
            ...array_key_exists('booking', $data)
                ? ['booking' => array_filter($data['booking'] ?? [], fn ($value) => $value !== null && $value !== '')]
                : [],
        ];
    }
}
