<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitFinalThesisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submitFinal', $this->route('thesis'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
