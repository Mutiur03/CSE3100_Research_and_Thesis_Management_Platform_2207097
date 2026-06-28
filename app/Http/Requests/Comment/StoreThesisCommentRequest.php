<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Models\Thesis;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThesisCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent() || $this->user()->isSupervisor();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Thesis $thesis */
        $thesis = $this->route('thesis');

        return [
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(function ($query) use ($thesis) {
                    $query->where('commentable_type', Thesis::class)
                        ->where('commentable_id', $thesis->id)
                        ->whereNull('parent_id');
                }),
            ],
            'is_private' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->isStudent()) {
            $this->merge(['is_private' => false]);
        }
    }

    public function isPrivate(): bool
    {
        return $this->user()->isSupervisor() && $this->boolean('is_private');
    }
}
