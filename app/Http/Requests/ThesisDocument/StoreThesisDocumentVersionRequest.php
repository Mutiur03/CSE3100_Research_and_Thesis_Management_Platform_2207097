<?php

namespace App\Http\Requests\ThesisDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreThesisDocumentVersionRequest extends FormRequest
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
            'file' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
                    ->max(10 * 1024),
            ],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
