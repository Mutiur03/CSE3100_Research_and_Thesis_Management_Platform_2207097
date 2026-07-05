<?php

namespace Database\Seeders;

use App\Enums\DocumentCategory;
use App\Enums\MeetingRsvpStatus;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Enums\MilestoneStatus;
use App\Enums\ProposalStatus;
use App\Enums\ThesisReviewStatus;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Models\ThesisReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        $password = Hash::make(self::PASSWORD);

        $department = Department::query()->create([
            'name' => 'Computer Science & Engineering',
            'code' => 'CSE',
            'faculty' => 'Faculty of Science & Engineering',
            'description' => 'Department of Computer Science and Engineering for the showcase demo.',
        ]);

        $admin = User::query()->create([
            'name' => 'Dr. Admin Rahman',
            'email' => 'admin@researchhub.test',
            'password' => $password,
            'role' => UserRole::Admin,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'bio' => 'Platform administrator for institutional thesis oversight.',
        ]);

        $supervisor = User::query()->create([
            'name' => 'Dr. Sarah Khan',
            'email' => 'supervisor@researchhub.test',
            'password' => $password,
            'role' => UserRole::Supervisor,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'research_interests' => ['Machine Learning', 'NLP', 'Software Engineering'],
        ]);

        $student = User::query()->create([
            'name' => 'Mutiur Rahman',
            'email' => 'student@researchhub.test',
            'password' => $password,
            'role' => UserRole::Student,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'research_interests' => ['Machine Learning', 'Data Science'],
        ]);

        $reviewer = User::query()->create([
            'name' => 'Prof. Ahmed Hossain',
            'email' => 'reviewer@researchhub.test',
            'password' => $password,
            'role' => UserRole::Reviewer,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $pendingStudent = User::query()->create([
            'name' => 'Nadia Islam',
            'email' => 'student2@researchhub.test',
            'password' => $password,
            'role' => UserRole::Student,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Proposal::query()->create([
            'student_id' => $pendingStudent->id,
            'department_id' => $department->id,
            'supervisor_id' => $supervisor->id,
            'title' => 'Blockchain-Based Academic Credential Verification',
            'abstract' => 'A decentralized approach to verifying academic credentials using blockchain technology.',
            'objectives' => "1. Design a credential schema\n2. Build a prototype verification portal\n3. Evaluate security and scalability",
            'methodology' => 'Smart contract development on Ethereum testnet with a Laravel verification API.',
            'status' => ProposalStatus::Submitted,
            'submitted_at' => now()->subDay(),
        ]);

        $proposal = Proposal::query()->create([
            'student_id' => $student->id,
            'department_id' => $department->id,
            'supervisor_id' => $supervisor->id,
            'title' => 'AI-Powered Thesis Plagiarism Detection for Bengali Text',
            'abstract' => 'This research proposes a machine learning pipeline to detect semantic plagiarism in Bengali academic writing, combining transformer embeddings with institutional document repositories.',
            'objectives' => "1. Build a Bengali text embedding model\n2. Compare against existing English-only tools\n3. Integrate with a thesis management workflow",
            'methodology' => 'Fine-tuned multilingual transformers, vector similarity search, and evaluation on a curated Bengali thesis corpus.',
            'status' => ProposalStatus::Approved,
            'submitted_at' => now()->subMonths(2),
            'reviewed_at' => now()->subMonths(2)->addDay(),
        ]);

        $thesis = Thesis::createFromApprovedProposal($proposal);

        Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Literature review complete',
            'description' => 'Survey existing plagiarism detection systems.',
            'due_date' => now()->subMonth(),
            'status' => MilestoneStatus::Completed,
            'progress_percentage' => 100,
            'completed_at' => now()->subWeeks(3),
            'sort_order' => 1,
            'created_by' => $supervisor->id,
        ]);

        Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Model training and evaluation',
            'description' => 'Train embedding model and benchmark results.',
            'due_date' => now()->addWeeks(2),
            'status' => MilestoneStatus::InProgress,
            'progress_percentage' => 60,
            'sort_order' => 2,
            'created_by' => $supervisor->id,
        ]);

        Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Final thesis draft',
            'description' => 'Complete written thesis and prepare for external review.',
            'due_date' => now()->addMonth(),
            'status' => MilestoneStatus::Pending,
            'progress_percentage' => 0,
            'sort_order' => 3,
            'created_by' => $supervisor->id,
        ]);

        $meeting = Meeting::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Weekly supervision check-in',
            'description' => 'Review model evaluation progress.',
            'type' => MeetingType::Supervision,
            'scheduled_at' => now()->addDays(3)->setTime(14, 0),
            'duration_minutes' => 60,
            'location' => 'Room 204, CSE Building',
            'agenda' => 'Discuss evaluation metrics and next experiments.',
            'status' => MeetingStatus::Scheduled,
            'organized_by' => $supervisor->id,
        ]);

        $meeting->attendees()->createMany([
            ['user_id' => $student->id, 'rsvp_status' => MeetingRsvpStatus::Pending],
            ['user_id' => $supervisor->id, 'rsvp_status' => MeetingRsvpStatus::Accepted],
        ]);

        $this->seedDemoDocument($thesis, $student, 'Chapter 1 - Introduction', DocumentCategory::Chapter);
        $this->seedDemoDocument($thesis, $student, 'Final Thesis Draft', DocumentCategory::Final);

        Comment::query()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesis->id,
            'user_id' => $supervisor->id,
            'body' => 'Good progress on the literature review. Please add more Bengali-language baseline comparisons in the next draft.',
            'is_private' => false,
        ]);

        ThesisReview::query()->create([
            'thesis_id' => $thesis->id,
            'reviewer_id' => $reviewer->id,
            'status' => ThesisReviewStatus::Pending,
            'assigned_by' => $supervisor->id,
            'assigned_at' => now()->subDays(2),
        ]);

        $this->command?->info('Demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->info('Demo accounts (password: '.self::PASSWORD.')');
        $this->command?->table(
            ['Role', 'Email'],
            [
                ['Admin', $admin->email],
                ['Supervisor', $supervisor->email],
                ['Student', $student->email],
                ['Reviewer', $reviewer->email],
                ['Student (pending proposal)', $pendingStudent->email],
            ],
        );
    }

    private function seedDemoDocument(Thesis $thesis, User $uploader, string $title, DocumentCategory $category): void
    {
        Storage::disk('public')->makeDirectory('demo');

        $fileName = str($title)->slug().'.txt';
        $filePath = 'demo/'.$fileName;
        Storage::disk('public')->put($filePath, "Demo document: {$title}\nThesis: {$thesis->title}");

        $document = ThesisDocument::query()->create([
            'thesis_id' => $thesis->id,
            'title' => $title,
            'description' => 'Seeded demo document for showcase.',
            'category' => $category,
            'current_version' => 1,
            'uploaded_by' => $uploader->id,
        ]);

        ThesisDocumentVersion::query()->create([
            'thesis_document_id' => $document->id,
            'version_number' => 1,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => Storage::disk('public')->size($filePath),
            'mime_type' => 'text/plain',
            'change_summary' => 'Initial demo upload',
            'checksum' => hash_file('sha256', Storage::disk('public')->path($filePath)),
            'uploaded_by' => $uploader->id,
            'created_at' => now(),
        ]);
    }
}
