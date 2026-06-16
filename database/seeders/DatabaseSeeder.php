<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Admin ────────────────────────────────
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@university.edu',
            'bio' => 'Platform administrator for the Research & Thesis Management System.',
        ]);

        // ── Supervisors ──────────────────────────
        User::factory()->supervisor()->create([
            'name' => 'Dr. Sarah Ahmed',
            'email' => 'sarah.ahmed@university.edu',
            'bio' => 'Associate Professor in Computer Science with expertise in Machine Learning and NLP.',
            'research_interests' => ['Machine Learning', 'Natural Language Processing', 'Deep Learning'],
        ]);

        User::factory()->supervisor()->create([
            'name' => 'Prof. Rahman Khan',
            'email' => 'rahman.khan@university.edu',
            'bio' => 'Professor of Software Engineering with 15 years of research experience.',
            'research_interests' => ['Software Engineering', 'Cloud Computing', 'DevOps'],
        ]);

        // ── Students ─────────────────────────────
        User::factory()->student()->count(5)->create();

        // ── Reviewer ─────────────────────────────
        User::factory()->reviewer()->create([
            'name' => 'Dr. Fatima Begum',
            'email' => 'fatima.begum@university.edu',
            'bio' => 'External reviewer specializing in Data Science and Statistical Methods.',
            'research_interests' => ['Data Science', 'Statistics', 'Bioinformatics'],
        ]);
    }
}
