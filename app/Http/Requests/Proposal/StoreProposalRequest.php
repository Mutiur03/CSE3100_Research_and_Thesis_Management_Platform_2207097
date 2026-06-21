<?php

namespace App\Http\Requests\Proposal;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'methodology' => ['nullable', 'string', 'max:5000'],
            'supervisor_id' => ['required', 'integer', 'exists:users,id', $this->supervisorRule()],
        ];
    }

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private function supervisorRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $supervisor = User::find($value);

            if (! $supervisor || ! $supervisor->isSupervisor() || ! $supervisor->is_active) {
                $fail('Please select an active supervisor.');
            }
        };
    }
}
