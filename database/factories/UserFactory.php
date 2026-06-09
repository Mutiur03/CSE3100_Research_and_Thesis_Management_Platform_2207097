<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement([UserRole::Student, UserRole::Supervisor]),
            'bio' => fake()->optional(0.6)->sentence(12),
            'phone' => fake()->optional(0.5)->phoneNumber(),
            'research_interests' => fake()->optional(0.5)->randomElements(
                ['Machine Learning', 'NLP', 'Computer Vision', 'Data Science', 'Cybersecurity', 'IoT', 'Cloud Computing', 'Blockchain', 'Bioinformatics', 'Robotics', 'Software Engineering', 'HCI'],
                fake()->numberBetween(2, 5)
            ),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set the user as a student.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Student,
        ]);
    }

    /**
     * Set the user as a supervisor.
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Supervisor,
        ]);
    }

    /**
     * Set the user as a reviewer.
     */
    public function reviewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Reviewer,
        ]);
    }

    /**
     * Set the user as an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * Set the user as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
