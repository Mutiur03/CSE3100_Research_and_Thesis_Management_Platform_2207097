<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Comment;
use App\Models\Thesis;

class ThesisCommentPostedNotification extends PlatformNotification
{
    public function __construct(
        public readonly Comment $comment,
        public readonly Thesis $thesis,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Comments;
    }

    public function title(): string
    {
        return 'New thesis comment';
    }

    public function message(): string
    {
        return sprintf(
            '%s posted a comment on "%s".',
            $this->comment->user->name,
            $this->thesis->title,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return $this->thesis->showUrlFor($notifiable);
    }
}
