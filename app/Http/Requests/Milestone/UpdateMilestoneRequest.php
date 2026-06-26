<?php

namespace App\Http\Requests\Milestone;

use App\Models\Milestone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMilestoneRequest extends FormRequest
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

            if (! $dependsOnId) {
                return;
            }

            /** @var Milestone $milestone */
            $milestone = $this->route('milestone');

            if ((int) $dependsOnId === $milestone->id) {
                $validator->errors()->add('depends_on_id', 'A milestone cannot depend on itself.');

                return;
            }

            if (Milestone::wouldCreateDependencyCycle($milestone->id, (int) $dependsOnId)) {
                $validator->errors()->add('depends_on_id', 'This dependency would create a cycle.');
            }
        });
    }
}
