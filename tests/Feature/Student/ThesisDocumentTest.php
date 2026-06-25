<?php

namespace Tests\Feature\Student;

use App\Enums\DocumentCategory;
use App\Models\ThesisDocument;
use App\Models\ThesisDocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class ThesisDocumentTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_student_can_upload_document_on_their_thesis(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $file = UploadedFile::fake()->create('chapter-one.pdf', 512, 'application/pdf');

        $this->actingAs($student)
            ->post(route('student.theses.documents.store', $thesis), [
                'title' => 'Chapter 1',
                'category' => DocumentCategory::Chapter->value,
                'file' => $file,
                'change_summary' => 'Initial draft',
            ])
            ->assertRedirect(route('student.theses.show', $thesis))
            ->assertSessionHas('success');

        $document = ThesisDocument::query()->where('thesis_id', $thesis->id)->firstOrFail();

        $this->assertSame('Chapter 1', $document->title);
        $this->assertSame(DocumentCategory::Chapter, $document->category);
        $this->assertSame(1, $document->current_version);
        $this->assertCount(1, $document->versions);

        Storage::disk('public')->assertExists($document->latestVersion->file_path);
    }

    public function test_student_can_upload_new_version_and_download_historical_version(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $document = ThesisDocument::factory()->create([
            'thesis_id' => $thesis->id,
            'uploaded_by' => $student->id,
            'current_version' => 1,
        ]);
        $firstVersion = ThesisDocumentVersion::factory()->create([
            'thesis_document_id' => $document->id,
            'version_number' => 1,
            'uploaded_by' => $student->id,
            'file_path' => 'thesis-documents/'.$thesis->id.'/'.$document->id.'/v1/first.pdf',
            'file_name' => 'first.pdf',
        ]);
        Storage::disk('public')->put($firstVersion->file_path, 'first version');

        $secondFile = UploadedFile::fake()->create('second.pdf', 600, 'application/pdf');

        $this->actingAs($student)
            ->post(route('student.theses.documents.versions.store', [$thesis, $document]), [
                'file' => $secondFile,
                'change_summary' => 'Revised introduction',
            ])
            ->assertRedirect(route('student.theses.show', $thesis))
            ->assertSessionHas('success');

        $document->refresh();

        $this->assertSame(2, $document->current_version);
        $this->assertDatabaseCount('thesis_document_versions', 2);

        $this->actingAs($student)
            ->get(route('student.theses.documents.versions.download', [$thesis, $document, $firstVersion]))
            ->assertOk()
            ->assertDownload('first.pdf');
    }

    public function test_student_cannot_upload_document_on_another_students_thesis(): void
    {
        ['thesis' => $thesis] = $this->createSupervisedThesis();
        $otherStudent = User::factory()->student()->create();
        $file = UploadedFile::fake()->create('draft.pdf', 256, 'application/pdf');

        $this->actingAs($otherStudent)
            ->post(route('student.theses.documents.store', $thesis), [
                'title' => 'Blocked upload',
                'category' => DocumentCategory::Other->value,
                'file' => $file,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('thesis_documents', 0);
    }

    public function test_cross_thesis_document_version_upload_returns_not_found(): void
    {
        $contextA = $this->createSupervisedThesis();
        $contextB = $this->createSupervisedThesis();
        $document = ThesisDocument::factory()->create([
            'thesis_id' => $contextB['thesis']->id,
            'uploaded_by' => $contextB['student']->id,
        ]);
        $file = UploadedFile::fake()->create('blocked.pdf', 256, 'application/pdf');

        $this->actingAs($contextA['student'])
            ->post(route('student.theses.documents.versions.store', [$contextA['thesis'], $document]), [
                'file' => $file,
            ])
            ->assertNotFound();
    }
}
