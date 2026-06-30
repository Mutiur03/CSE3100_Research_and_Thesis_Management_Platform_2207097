<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\ProposalStatus;
use App\Models\Proposal;

class ProposalReviewedNotification extends PlatformNotification
{
    public function __construct(
        public readonly Proposal $proposal,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Proposals;
    }

    public function title(): string
    {
        return match ($this->proposal->status) {
            ProposalStatus::Approved => 'Proposal approved',
            ProposalStatus::Rejected => 'Proposal rejected',
            ProposalStatus::RevisionRequested => 'Revision requested',
            default => 'Proposal updated',
        };
    }

    public function message(): string
    {
        $supervisor = $this->proposal->supervisor->name;

        return match ($this->proposal->status) {
            ProposalStatus::Approved => sprintf('Your supervisor %s approved your proposal "%s".', $supervisor, $this->proposal->title),
            ProposalStatus::Rejected => sprintf('Your supervisor %s rejected your proposal "%s".', $supervisor, $this->proposal->title),
            ProposalStatus::RevisionRequested => sprintf('Your supervisor %s requested revisions on "%s".', $supervisor, $this->proposal->title),
            default => sprintf('Your proposal "%s" was updated.', $this->proposal->title),
        };
    }

    public function actionUrl(object $notifiable): string
    {
        return route('student.proposals.show', $this->proposal);
    }
}
