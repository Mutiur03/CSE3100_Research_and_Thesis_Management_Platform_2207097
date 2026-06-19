<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Computer Science & Engineering',
            'Electrical & Electronic Engineering',
            'Mathematics',
            'Physics',
            'Business Administration',
            'Civil Engineering',
            'Mechanical Engineering',
            'Pharmacy',
        ]);

        return [
            'name' => $name,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'faculty' => fake()->optional(0.8)->randomElement([
                'Faculty of Science & Engineering',
                'Faculty of Business Studies',
                'Faculty of Arts & Humanities',
            ]),
            'description' => fake()->optional(0.7)->sentence(12),
        ];
    }
}
