<?php

namespace App\Http\Requests\Milestone;

use App\Enums\MilestoneTaskPriority;
use App\Enums\MilestoneTaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMilestoneTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'status' => ['required', Rule::enum(MilestoneTaskStatus::class)],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
