<?php

namespace App\Http\Requests\Host;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Host;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'province_code' => ['required', 'string', 'exists:provinces,code'],
            'address' => ['required', 'string', 'max:255'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'new_price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
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
