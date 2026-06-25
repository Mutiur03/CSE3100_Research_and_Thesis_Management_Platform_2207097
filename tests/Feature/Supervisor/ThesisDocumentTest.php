<?php

namespace Tests\Feature\Supervisor;

use App\Enums\DocumentCategory;
use App\Models\ThesisDocument;
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

    public function test_supervisor_can_upload_document_on_supervised_thesis(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();
        $file = UploadedFile::fake()->create('feedback-notes.pdf', 400, 'application/pdf');

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.documents.store', $thesis), [
                'title' => 'Supervisor notes',
                'category' => DocumentCategory::Other->value,
                'file' => $file,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('thesis_documents', [
            'thesis_id' => $thesis->id,
            'title' => 'Supervisor notes',
            'uploaded_by' => $supervisor->id,
        ]);
    }

    public function test_other_supervisor_cannot_upload_document(): void
    {
        ['thesis' => $thesis] = $this->createSupervisedThesis();
        $otherSupervisor = User::factory()->supervisor()->create();
        $file = UploadedFile::fake()->create('blocked.pdf', 256, 'application/pdf');

        $this->actingAs($otherSupervisor)
            ->post(route('supervisor.theses.documents.store', $thesis), [
                'title' => 'Blocked',
                'category' => DocumentCategory::Other->value,
                'file' => $file,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('thesis_documents', 0);
    }
}
