<?php

namespace App\Http\Requests\ThesisDocument;

use App\Enums\DocumentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreThesisDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [\App\Models\ThesisDocument::class, $this->route('thesis')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'file' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
                    ->max(10 * 1024),
            ],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
