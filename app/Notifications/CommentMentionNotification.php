<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Comment;
use App\Models\Thesis;

class CommentMentionNotification extends PlatformNotification
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
        return 'You were mentioned';
    }

    public function message(): string
    {
        return sprintf(
            '%s mentioned you in a comment on "%s".',
            $this->comment->user->name,
            $this->thesis->title,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return $this->thesis->showUrlFor($notifiable);
    }
}
