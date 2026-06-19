<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'research_interests' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.max' => 'The avatar image must not exceed 2MB.',
            'avatar.image' => 'The avatar must be a valid image file.',
        ];
    }

    /**
     * Parse research interests from comma-separated string to array.
     *
     * @return array<string>|null
     */
    public function parsedResearchInterests(): ?array
    {
        $raw = $this->input('research_interests');

        if (empty($raw)) {
            return null;
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $raw))
        ));
    }
}
