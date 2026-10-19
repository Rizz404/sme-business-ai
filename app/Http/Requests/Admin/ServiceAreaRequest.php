<?php

namespace App\Http\Requests\Admin;

use App\Modules\ServiceArea\Enums\AreaType;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Create or update a service area, with the photos added or removed in the
 * same form.
 */
class ServiceAreaRequest extends FormRequest
{
    /**
     * A record of another business is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('service_area');

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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.ja.name' => ['nullable', 'string'],
            'translations.ja.description' => ['nullable', 'string'],
            'area_type' => ['required', 'string', Rule::in(AreaType::values())],
            'min_attendees' => ['required', 'integer', 'min:1', 'max:30'],
            'max_attendees' => ['required', 'integer', 'min:1', 'max:30', 'gte:min_attendees'],
            'features' => ['nullable', 'string'],
            'booking_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * The columns to write, without the slug (which only a new area gets).
     *
     * @return array<string, mixed>
     */
    public function serviceAreaAttributes(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'translations' => [
                'en' => ['name' => $data['translations']['en']['name'] ?? null, 'description' => $data['translations']['en']['description'] ?? null],
                'ja' => ['name' => $data['translations']['ja']['name'] ?? null, 'description' => $data['translations']['ja']['description'] ?? null],
            ],
            'area_type' => $data['area_type'],
            'min_attendees' => $data['min_attendees'],
            'max_attendees' => $data['max_attendees'],
            'features' => Text::slugList($data['features'] ?? null),
            'booking_fee' => $data['booking_fee'],
            'minimum_order' => $data['minimum_order'] ?? null,
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }

    /**
     * @return list<int>
     */
    public function imageIdsToDelete(): array
    {
        return collect($this->input('delete_images', []))->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return list<array{url: string, alt?: ?string, tags?: ?string}>
     */
    public function newImages(): array
    {
        return collect($this->input('new_images', []))
            ->filter(fn ($row) => filled($row['url'] ?? null))
            ->values()
            ->all();
    }
}
