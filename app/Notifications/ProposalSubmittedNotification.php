<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Proposal;

class ProposalSubmittedNotification extends PlatformNotification
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
        return 'New proposal submitted';
    }

    public function message(): string
    {
        return sprintf(
            '%s submitted the proposal "%s" for your review.',
            $this->proposal->student->name,
            $this->proposal->title,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return route('supervisor.proposals.show', $this->proposal);
    }
}
