<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_num', 'unique:departments,code'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'head_id' => ['nullable', 'integer', 'exists:users,id', $this->eligibleHeadRule()],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper($this->input('code')),
            ]);
        }
    }

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private function eligibleHeadRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null) {
                return;
            }

            $head = User::find($value);

            if (! $head || ! in_array($head->role, [UserRole::Supervisor, UserRole::Admin], true)) {
                $fail('The department head must be a supervisor or administrator.');
            }
        };
    }
}
