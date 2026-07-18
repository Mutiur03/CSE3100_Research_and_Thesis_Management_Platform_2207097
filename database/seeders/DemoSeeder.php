<?php

namespace Database\Seeders;

use App\Enums\DocumentCategory;
use App\Enums\MeetingFormat;
use App\Enums\MeetingRsvpStatus;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Enums\MilestoneStatus;
use App\Enums\MilestoneTaskPriority;
use App\Enums\MilestoneTaskStatus;
use App\Enums\ProposalStatus;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\MilestoneTask;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
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
            'description' => 'Department of Computer Science and Engineering for undergraduate and postgraduate research.',
        ]);

        $supervisor = User::query()->create([
            'name' => 'Dr. Mutiur Rahman',
            'email' => 'mutiur@alturavent.com',
            'password' => $password,
            'role' => UserRole::Supervisor,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '01711000001',
            'bio' => 'Associate Professor, CSE. Research focus: machine learning, NLP, and academic research systems.',
            'research_interests' => ['Machine Learning', 'Natural Language Processing', 'Software Engineering'],
        ]);

        $department->update(['head_id' => $supervisor->id]);

        $student = User::query()->create([
            'name' => 'Mutiur Rahman',
            'email' => 'mutiur5bb@gmail.com',
            'password' => $password,
            'role' => UserRole::Student,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '01711000002',
            'bio' => 'Final-year CSE student working on AI-assisted academic integrity tools.',
            'research_interests' => ['Machine Learning', 'Data Science', 'Information Retrieval'],
        ]);

        $pendingStudent = User::query()->create([
            'name' => 'Arif Mahmud',
            'email' => 'helloworldkuet@gmail.com',
            'password' => $password,
            'role' => UserRole::Student,
            'department_id' => $department->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'phone' => '01711000003',
            'bio' => 'CSE student at KUET interested in distributed systems and academic credential security.',
            'research_interests' => ['Blockchain', 'Cybersecurity', 'Distributed Systems'],
        ]);

        Proposal::query()->create([
            'student_id' => $pendingStudent->id,
            'department_id' => $department->id,
            'supervisor_id' => $supervisor->id,
            'title' => 'Blockchain-Based Academic Credential Verification',
            'abstract' => <<<'TXT'
Academic institutions still verify certificates through slow, paper-based channels that are easy to forge.
This proposal introduces a decentralized verification workflow that stores credential hashes on a permissioned blockchain and exposes a public verification portal for employers and universities.
The system aims to reduce verification time while preserving institutional control over credential issuance.
TXT,
            'objectives' => <<<'TXT'
1. Design a credential schema suitable for undergraduate and postgraduate certificates.
2. Implement smart-contract based issuance and revocation on an Ethereum testnet.
3. Build a Laravel verification API and portal for third-party checks.
4. Evaluate security, latency, and operational cost against current manual verification.
TXT,
            'methodology' => <<<'TXT'
1. Requirements analysis with the exam controller office.
2. Smart-contract development and unit testing on a public testnet.
3. Backend integration using Laravel for issuer workflows and audit logs.
4. Controlled pilot with synthetic credentials and security review.
TXT,
            'status' => ProposalStatus::Submitted,
            'submitted_at' => now()->subDay(),
        ]);

        $proposal = Proposal::query()->create([
            'student_id' => $student->id,
            'department_id' => $department->id,
            'supervisor_id' => $supervisor->id,
            'title' => 'AI-Powered Thesis Plagiarism Detection for Bengali Text',
            'abstract' => <<<'TXT'
Existing plagiarism tools are optimized for English and often miss paraphrased Bengali academic writing.
This research proposes a semantic plagiarism detection pipeline that combines multilingual transformer embeddings with institutional thesis repositories.
The goal is to support supervisors with ranked similarity reports that highlight suspected reused passages across Bengali and mixed-language submissions.
TXT,
            'objectives' => <<<'TXT'
1. Curate a labeled Bengali thesis corpus for similarity evaluation.
2. Fine-tune a multilingual embedding model for academic Bengali text.
3. Benchmark against lexical baselines and English-centric commercial tools.
4. Integrate detection results into a thesis supervision workflow.
TXT,
            'methodology' => <<<'TXT'
1. Collect and anonymize institutional thesis samples with ethics approval.
2. Train and evaluate embedding models using cosine similarity and ranking metrics.
3. Build an API that returns passage-level similarity scores and evidence snippets.
4. Run a supervised pilot with faculty reviewers to measure usefulness and false-positive rate.
TXT,
            'status' => ProposalStatus::Approved,
            'submitted_at' => now()->subMonths(2),
            'reviewed_at' => now()->subMonths(2)->addDays(2),
            'review_notes' => 'Approved. Focus the evaluation chapter on Bengali baselines and document dataset limitations clearly.',
        ]);

        $thesis = Thesis::createFromApprovedProposal($proposal);

        $literatureMilestone = Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Literature review complete',
            'description' => 'Survey plagiarism detection systems, Bengali NLP resources, and evaluation metrics.',
            'due_date' => now()->subMonth()->toDateString(),
            'status' => MilestoneStatus::Completed,
            'progress_percentage' => 100,
            'completed_at' => now()->subWeeks(3),
            'sort_order' => 1,
            'created_by' => $supervisor->id,
        ]);

        MilestoneTask::query()->create([
            'milestone_id' => $literatureMilestone->id,
            'title' => 'Collect related papers',
            'description' => 'Gather at least 25 peer-reviewed papers on semantic plagiarism and Bengali NLP.',
            'assigned_to' => $student->id,
            'status' => MilestoneTaskStatus::Completed,
            'priority' => MilestoneTaskPriority::High,
            'due_date' => now()->subWeeks(6)->toDateString(),
            'completed_at' => now()->subWeeks(5),
            'created_by' => $supervisor->id,
        ]);

        MilestoneTask::query()->create([
            'milestone_id' => $literatureMilestone->id,
            'title' => 'Write literature summary',
            'description' => 'Summarize gaps in English-centric tools for Bengali academic text.',
            'assigned_to' => $student->id,
            'status' => MilestoneTaskStatus::Completed,
            'priority' => MilestoneTaskPriority::Medium,
            'due_date' => now()->subMonth()->toDateString(),
            'completed_at' => now()->subWeeks(3),
            'created_by' => $supervisor->id,
        ]);

        $modelMilestone = Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Model training and evaluation',
            'description' => 'Train embedding model, run benchmarks, and document results.',
            'due_date' => now()->addWeeks(2)->toDateString(),
            'status' => MilestoneStatus::InProgress,
            'progress_percentage' => 50,
            'sort_order' => 2,
            'depends_on_id' => $literatureMilestone->id,
            'created_by' => $supervisor->id,
        ]);

        MilestoneTask::query()->create([
            'milestone_id' => $modelMilestone->id,
            'title' => 'Prepare training dataset',
            'description' => 'Clean, tokenize, and split the Bengali thesis corpus.',
            'assigned_to' => $student->id,
            'status' => MilestoneTaskStatus::Completed,
            'priority' => MilestoneTaskPriority::High,
            'due_date' => now()->subWeek()->toDateString(),
            'completed_at' => now()->subDays(5),
            'created_by' => $supervisor->id,
        ]);

        MilestoneTask::query()->create([
            'milestone_id' => $modelMilestone->id,
            'title' => 'Run baseline experiments',
            'description' => 'Compare TF-IDF, multilingual MiniLM, and fine-tuned embeddings.',
            'assigned_to' => $student->id,
            'status' => MilestoneTaskStatus::InProgress,
            'priority' => MilestoneTaskPriority::Urgent,
            'due_date' => now()->addWeek()->toDateString(),
            'created_by' => $supervisor->id,
        ]);

        $finalMilestone = Milestone::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Final thesis draft',
            'description' => 'Complete written thesis chapters and prepare final submission package.',
            'due_date' => now()->addMonth()->toDateString(),
            'status' => MilestoneStatus::Pending,
            'progress_percentage' => 0,
            'sort_order' => 3,
            'depends_on_id' => $modelMilestone->id,
            'created_by' => $supervisor->id,
        ]);

        MilestoneTask::query()->create([
            'milestone_id' => $finalMilestone->id,
            'title' => 'Draft evaluation chapter',
            'description' => 'Write results, discussion, and limitations after experiments finish.',
            'assigned_to' => $student->id,
            'status' => MilestoneTaskStatus::Todo,
            'priority' => MilestoneTaskPriority::Medium,
            'due_date' => now()->addWeeks(3)->toDateString(),
            'created_by' => $supervisor->id,
        ]);

        $meeting = Meeting::query()->create([
            'thesis_id' => $thesis->id,
            'title' => 'Weekly supervision check-in',
            'description' => 'Review model evaluation progress and next experiment plan.',
            'type' => MeetingType::Supervision,
            'format' => MeetingFormat::InPerson,
            'scheduled_at' => now()->addDays(3)->setTime(14, 0),
            'duration_minutes' => 60,
            'location' => 'Room 204, CSE Building',
            'meeting_link' => null,
            'agenda' => <<<'TXT'
1. Review baseline experiment results
2. Decide metric thresholds for plagiarism alerts
3. Plan evaluation chapter outline
4. Confirm next document upload deadline
TXT,
            'status' => MeetingStatus::Scheduled,
            'organized_by' => $supervisor->id,
        ]);

        $meeting->attendees()->createMany([
            ['user_id' => $student->id, 'rsvp_status' => MeetingRsvpStatus::Pending],
            ['user_id' => $supervisor->id, 'rsvp_status' => MeetingRsvpStatus::Accepted],
        ]);

        $this->seedDemoDocument(
            $thesis,
            $student,
            'Chapter 1 - Introduction',
            DocumentCategory::Chapter,
            'Initial chapter draft covering motivation, problem statement, and research questions.',
            'Initial upload',
        );

        $this->seedDemoDocument(
            $thesis,
            $student,
            'Final Thesis Draft',
            DocumentCategory::Final,
            'Working final draft for supervisor review before formal submission.',
            'First complete draft',
        );

        $publicComment = Comment::query()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesis->id,
            'user_id' => $supervisor->id,
            'body' => 'Good progress on the literature review. Please add more Bengali-language baseline comparisons in the next draft.',
            'is_private' => false,
        ]);

        Comment::query()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesis->id,
            'user_id' => $student->id,
            'parent_id' => $publicComment->id,
            'body' => 'Noted. I will include BanglaBERT and a lexical TF-IDF baseline in the evaluation table.',
            'is_private' => false,
        ]);

        Comment::query()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesis->id,
            'user_id' => $supervisor->id,
            'body' => 'Private note: push the student to document dataset licensing constraints before the final chapter.',
            'is_private' => true,
        ]);

        $this->command?->info('Demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->info('Create the first admin via /setup, then use these accounts (password: '.self::PASSWORD.')');
        $this->command?->table(
            ['Role', 'Email'],
            [
                ['Supervisor', $supervisor->email],
                ['Student (active thesis)', $student->email],
                ['Student (pending proposal)', $pendingStudent->email],
            ],
        );
    }

    private function seedDemoDocument(
        Thesis $thesis,
        User $uploader,
        string $title,
        DocumentCategory $category,
        string $description,
        string $changeSummary,
    ): void {
        Storage::disk('public')->makeDirectory('demo');

        $fileName = str($title)->slug().'.pdf';
        $filePath = 'demo/'.$fileName;
        $pdf = $this->minimalPdf("{$title}\n\nThesis: {$thesis->title}\n\n{$description}");

        Storage::disk('public')->put($filePath, $pdf);

        $document = ThesisDocument::query()->create([
            'thesis_id' => $thesis->id,
            'title' => $title,
            'description' => $description,
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
            'mime_type' => 'application/pdf',
            'change_summary' => $changeSummary,
            'checksum' => hash_file('sha256', Storage::disk('public')->path($filePath)),
            'uploaded_by' => $uploader->id,
            'created_at' => now(),
        ]);
    }

    private function minimalPdf(string $text): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $lines = preg_split("/\r\n|\n|\r/", $escaped) ?: [$escaped];
        $content = "BT /F1 11 Tf 50 750 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -16 Td\n";
            }
            $content .= "({$line}) Tj\n";
        }

        $content .= 'ET';
        $length = strlen($content);

        return <<<PDF
%PDF-1.4
1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj
2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj
3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj
4 0 obj<< /Length {$length} >>stream
{$content}
endstream
endobj
5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj
xref
0 6
0000000000 65535 f 
trailer<< /Size 6 /Root 1 0 R >>
startxref
0
%%EOF
PDF;
    }
}
