<?php

namespace Database\Factories;

use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThesisDocumentVersion>
 */
class ThesisDocumentVersionFactory extends Factory
{
    protected $model = ThesisDocumentVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thesis_document_id' => ThesisDocument::factory(),
            'version_number' => 1,
            'file_path' => 'thesis-documents/1/1/v1/sample.pdf',
            'file_name' => 'sample.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'change_summary' => fake()->optional()->sentence(),
            'checksum' => hash('sha256', 'sample'),
            'uploaded_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
