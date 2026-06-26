<?php

namespace App\Http\Requests\Milestone;

use App\Enums\MilestoneTaskPriority;
use App\Models\MilestoneTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMilestoneTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [MilestoneTask::class, $this->route('milestone')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(MilestoneTaskPriority::class)],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
