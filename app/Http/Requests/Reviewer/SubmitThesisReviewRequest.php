<?php

namespace App\Http\Requests\Reviewer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitThesisReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submit', $this->route('thesisReview'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'request_revision'])],
            'review_notes' => [
                Rule::requiredIf(fn () => in_array($this->input('decision'), ['reject', 'request_revision'], true)),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
