<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Meeting;

class MeetingScheduledNotification extends PlatformNotification
{
    public function __construct(
        public readonly Meeting $meeting,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Meetings;
    }

    public function title(): string
    {
        return 'Meeting scheduled';
    }

    public function message(): string
    {
        return sprintf(
            'A %s meeting "%s" was scheduled for %s.',
            $this->meeting->type->label(),
            $this->meeting->title,
            $this->meeting->scheduled_at->format('M j, Y g:i A'),
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return $this->meeting->thesis->showUrlFor($notifiable);
    }
}
