<?php

namespace App\Http\Requests\Milestone;

use App\Models\Milestone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSupervisor();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Thesis $thesis */
        $thesis = $this->route('thesis');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'depends_on_id' => [
                'nullable',
                'integer',
                Rule::exists('milestones', 'id')->where(fn ($query) => $query->where('thesis_id', $thesis->id)),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $dependsOnId = $this->input('depends_on_id');

            if ($dependsOnId && Milestone::wouldCreateDependencyCycle(0, (int) $dependsOnId)) {
                $validator->errors()->add('depends_on_id', 'Invalid milestone dependency.');
            }
        });
    }
}
