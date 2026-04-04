<?php

namespace App\Http\Requests\Host;

use App\Enums\UserRole;
use App\Models\RoomType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()?->role !== UserRole::Host) {
            return false;
        }

        $roomType = $this->route('roomType');

        return $roomType instanceof RoomType
            && $roomType->hotel->host_id === (int) $this->user()->getAuthIdentifier();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'max_occupancy' => ['required', 'integer', 'min:1', 'max:30'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'area_sqm' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'new_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'bed_lines' => ['nullable', 'array'],
            'bed_lines.*.area_name' => ['nullable', 'string', 'max:120'],
            'bed_lines.*.bed_summary' => ['nullable', 'string', 'max:255'],
            'room_amenity_ids' => ['nullable', 'array'],
            'room_amenity_ids.*' => ['integer', 'exists:room_amenities,id'],
            'room_images' => ['nullable', 'array'],
            'room_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_room_image_ids' => ['nullable', 'array'],
            'remove_room_image_ids.*' => [
                'integer',
                Rule::exists('room_type_images', 'id')->where(fn ($q) => $q->where('room_type_id', $this->route('roomType')->id)),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $oldPrice = $this->input('old_price');
            $newPrice = $this->input('new_price');

            if ($oldPrice !== null && $oldPrice !== '' && (float) $oldPrice <= (float) $newPrice) {
                $validator->errors()->add('old_price', __('Giá cũ phải lớn hơn giá mới.'));
            }
        });
    }
}
