<?php

namespace Database\Factories;

use App\Enums\DocumentCategory;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThesisDocument>
 */
class ThesisDocumentFactory extends Factory
{
    protected $model = ThesisDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thesis_id' => Thesis::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(DocumentCategory::cases()),
            'current_version' => 1,
            'uploaded_by' => User::factory(),
        ];
    }
}
