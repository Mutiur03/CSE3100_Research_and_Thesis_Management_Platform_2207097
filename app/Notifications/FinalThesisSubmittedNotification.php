<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Thesis;
use App\Models\User;

class FinalThesisSubmittedNotification extends PlatformNotification
{
    public function __construct(
        public readonly Thesis $thesis,
        public readonly User $student,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Documents;
    }

    public function title(): string
    {
        return 'Final thesis submitted';
    }

    public function message(): string
    {
        return sprintf(
            '%s submitted the final thesis document for "%s".',
            $this->student->name,
            $this->thesis->title,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        if ($notifiable->isSupervisor()) {
            return route('supervisor.theses.show', $this->thesis);
        }

        return route('admin.theses.show', $this->thesis);
    }
}
