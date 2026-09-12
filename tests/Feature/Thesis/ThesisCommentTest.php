<?php

namespace Tests\Feature\Thesis;

use App\Models\Comment;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesThesisContext;
use Tests\TestCase;

class ThesisCommentTest extends TestCase
{
    use CreatesThesisContext;
    use RefreshDatabase;

    public function test_student_and_supervisor_can_post_comments_and_replies(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('student.theses.comments.store', $thesis), [
                'body' => 'Draft chapter uploaded for review.',
            ])
            ->assertRedirect(route('student.theses.show', $thesis))
            ->assertSessionHas('success');

        $comment = Comment::query()->where('commentable_id', $thesis->id)->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.comments.store', $thesis), [
                'body' => 'Thanks, I will review it this week.',
                'parent_id' => $comment->id,
            ])
            ->assertRedirect(route('supervisor.theses.show', $thesis))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('comments', 2);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'parent_id' => null,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseHas('comments', [
            'parent_id' => $comment->id,
            'user_id' => $supervisor->id,
        ]);
    }

    public function test_mentions_are_parsed_and_stored(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('student.theses.comments.store', $thesis), [
                'body' => 'Hi @'.$supervisor->email.', please review the latest draft.',
            ])
            ->assertRedirect(route('student.theses.show', $thesis));

        $comment = Comment::query()->where('commentable_id', $thesis->id)->firstOrFail();

        $this->assertDatabaseHas('comment_mentions', [
            'comment_id' => $comment->id,
            'user_id' => $supervisor->id,
        ]);
    }

    public function test_mentions_accept_full_name_with_spaces(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('student.theses.comments.store', $thesis), [
                'body' => 'Hi @'.$supervisor->name.', please review the latest draft.',
            ])
            ->assertRedirect(route('student.theses.show', $thesis));

        $comment = Comment::query()->where('commentable_id', $thesis->id)->firstOrFail();

        $this->assertDatabaseHas('comment_mentions', [
            'comment_id' => $comment->id,
            'user_id' => $supervisor->id,
        ]);
    }

    public function test_discussion_ui_lists_other_participants_for_mentions(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->get(route('student.theses.show', $thesis))
            ->assertOk()
            ->assertSee('Type', false)
            ->assertSee('Mention', false)
            ->assertSee($supervisor->name)
            ->assertDontSee('data-mention-email="'.$student->email.'"', false);
    }

    public function test_supervisor_private_comments_are_hidden_from_student_view(): void
    {
        ['student' => $student, 'supervisor' => $supervisor, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $this->actingAs($supervisor)
            ->post(route('supervisor.theses.comments.store', $thesis), [
                'body' => 'Internal supervisor note.',
                'is_private' => true,
            ]);

        $privateComment = Comment::query()->where('commentable_id', $thesis->id)->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.theses.show', $thesis))
            ->assertOk()
            ->assertDontSee('Internal supervisor note');

        $this->actingAs($supervisor)
            ->get(route('supervisor.theses.show', $thesis))
            ->assertOk()
            ->assertSee('Internal supervisor note');

        $this->assertFalse($privateComment->isVisibleTo($student));
        $this->assertTrue($privateComment->isVisibleTo($supervisor));
    }

    public function test_student_cannot_comment_on_another_students_thesis(): void
    {
        ['student' => $student] = $this->createSupervisedThesis();
        $otherContext = $this->createSupervisedThesis();

        $this->actingAs($student)
            ->post(route('student.theses.comments.store', $otherContext['thesis']), [
                'body' => 'Unauthorized comment',
            ])
            ->assertForbidden();
    }

    public function test_cross_thesis_comment_delete_returns_not_found(): void
    {
        ['supervisor' => $supervisor, 'thesis' => $thesisA] = $this->createSupervisedThesis();
        $otherContext = $this->createSupervisedThesis();
        $thesisB = $otherContext['thesis'];
        $thesisB->update(['supervisor_id' => $supervisor->id]);

        $commentOnB = Comment::factory()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesisB->id,
            'user_id' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)
            ->delete(route('supervisor.theses.comments.destroy', [$thesisA, $commentOnB]))
            ->assertNotFound();
    }

    public function test_author_can_delete_own_comment(): void
    {
        ['student' => $student, 'thesis' => $thesis] = $this->createSupervisedThesis();

        $comment = Comment::factory()->create([
            'commentable_type' => Thesis::class,
            'commentable_id' => $thesis->id,
            'user_id' => $student->id,
        ]);

        $this->actingAs($student)
            ->delete(route('student.theses.comments.destroy', [$thesis, $comment]))
            ->assertRedirect(route('student.theses.show', $thesis))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}
