<?php

namespace App\Http\Requests\Milestone;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
