<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Meeting;
use App\Models\Proposal;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\ThesisReview;
use App\Models\User;
use App\Notifications\CommentMentionNotification;
use App\Notifications\DocumentUploadedNotification;
use App\Notifications\MeetingScheduledNotification;
use App\Notifications\ProposalReviewedNotification;
use App\Notifications\ProposalSubmittedNotification;
use App\Notifications\ThesisCommentPostedNotification;
use Illuminate\Support\Collection;

class ThesisNotificationService
{
    public function notifyProposalSubmitted(Proposal $proposal): void
    {
        $proposal->loadMissing(['student', 'supervisor']);

        if ($proposal->supervisor) {
            $proposal->supervisor->notify(new ProposalSubmittedNotification($proposal));
        }

        User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->when($proposal->department_id, fn ($query) => $query->where('department_id', $proposal->department_id))
            ->each(fn (User $admin) => $admin->notify(new ProposalSubmittedNotification($proposal)));
    }

    public function notifyProposalReviewed(Proposal $proposal): void
    {
        $proposal->loadMissing(['student', 'supervisor']);

        $proposal->student->notify(new ProposalReviewedNotification($proposal));
    }

    public function notifyDocumentUploaded(Thesis $thesis, User $uploader, ThesisDocument $document): void
    {
        $thesis->loadMissing(['student', 'supervisor']);

        $recipients = collect([$thesis->student, $thesis->supervisor])
            ->filter(fn (?User $user) => $user && $user->id !== $uploader->id);

        $recipients->each(
            fn (User $recipient) => $recipient->notify(
                new DocumentUploadedNotification($thesis, $document, $uploader),
            ),
        );
    }

    public function notifyMeetingScheduled(Meeting $meeting): void
    {
        $meeting->loadMissing(['thesis.student', 'thesis.supervisor', 'attendees.user']);

        $meeting->attendees
            ->pluck('user')
            ->filter(fn (?User $user) => $user && $user->is_active)
            ->unique('id')
            ->each(fn (User $attendee) => $attendee->notify(new MeetingScheduledNotification($meeting)));
    }

    public function notifyCommentPosted(Comment $comment, Thesis $thesis): void
    {
        $comment->loadMissing(['user', 'mentions']);

        if ($comment->is_private) {
            $this->notifyMentions($comment, $thesis);

            return;
        }

        $participants = collect([$thesis->student, $thesis->supervisor])
            ->filter(fn (?User $user) => $user && $user->id !== $comment->user_id);

        $mentionedIds = $comment->mentions->pluck('id');

        $participants
            ->reject(fn (User $user) => $mentionedIds->contains($user->id))
            ->each(fn (User $recipient) => $recipient->notify(
                new ThesisCommentPostedNotification($comment, $thesis),
            ));

        $this->notifyMentions($comment, $thesis);
    }

    private function notifyMentions(Comment $comment, Thesis $thesis): void
    {
        $comment->mentions
            ->filter(fn (User $user) => $user->id !== $comment->user_id && $user->is_active)
            ->each(fn (User $mentioned) => $mentioned->notify(
                new CommentMentionNotification($comment, $thesis),
            ));
    }

    /**
     * @param  Collection<int, \App\Models\Milestone>  $dueSoon
     * @param  Collection<int, \App\Models\Milestone>  $overdue
     */
    public function notifyMilestoneReminders(User $user, Collection $dueSoon, Collection $overdue): void
    {
        if ($dueSoon->isNotEmpty()) {
            $user->notify(new \App\Notifications\MilestoneDueSoonNotification($dueSoon));
        }

        if ($overdue->isNotEmpty()) {
            $user->notify(new \App\Notifications\MilestoneOverdueNotification($overdue));
        }
    }

    public function notifyThesisReviewAssigned(ThesisReview $review): void
    {
        $review->loadMissing(['thesis.student', 'thesis.supervisor', 'reviewer']);

        $review->reviewer->notify(new \App\Notifications\ThesisReviewAssignedNotification($review));
    }

    public function notifyThesisReviewSubmitted(ThesisReview $review): void
    {
        $review->loadMissing(['thesis.student', 'thesis.supervisor', 'reviewer']);

        collect([$review->thesis->supervisor, $review->thesis->student])
            ->filter(fn (?User $user) => $user && $user->is_active)
            ->unique('id')
            ->each(fn (User $recipient) => $recipient->notify(
                new \App\Notifications\ThesisReviewSubmittedNotification($review),
            ));

        User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->when($review->thesis->department_id, fn ($query) => $query->where('department_id', $review->thesis->department_id))
            ->each(fn (User $admin) => $admin->notify(
                new \App\Notifications\ThesisReviewSubmittedNotification($review),
            ));
    }
}
