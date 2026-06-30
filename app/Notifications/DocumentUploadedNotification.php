<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Thesis;
use App\Models\ThesisDocument;
use App\Models\User;

class DocumentUploadedNotification extends PlatformNotification
{
    public function __construct(
        public readonly Thesis $thesis,
        public readonly ThesisDocument $document,
        public readonly User $uploader,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Documents;
    }

    public function title(): string
    {
        return 'New document uploaded';
    }

    public function message(): string
    {
        return sprintf(
            '%s uploaded "%s" on thesis "%s".',
            $this->uploader->name,
            $this->document->title,
            $this->thesis->title,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return $this->thesis->showUrlFor($notifiable);
    }
}
