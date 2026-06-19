<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manageRole', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::in(UserRole::assignableByAdminValues()),
            ],
            'is_active' => ['required', 'boolean'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'confirm_admin_promotion' => [
                Rule::excludeIf(fn () => $this->input('role') !== UserRole::Admin->value),
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirm_admin_promotion.accepted' => 'You must confirm administrator promotion before saving.',
            'confirm_admin_promotion.required' => 'You must confirm administrator promotion before saving.',
        ];
    }
}
