<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ThesisReview;

class ThesisReviewAssignedNotification extends PlatformNotification
{
    public function __construct(
        public readonly ThesisReview $review,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Reviews;
    }

    public function title(): string
    {
        return 'Thesis review assigned';
    }

    public function message(): string
    {
        return sprintf(
            'You have been assigned to review the thesis "%s" by %s.',
            $this->review->thesis->title,
            $this->review->thesis->student->name,
        );
    }

    public function actionUrl(object $notifiable): string
    {
        return route('reviewer.reviews.show', $this->review);
    }
}
