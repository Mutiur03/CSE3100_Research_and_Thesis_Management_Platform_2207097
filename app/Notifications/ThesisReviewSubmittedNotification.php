<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ThesisReview;

class ThesisReviewSubmittedNotification extends PlatformNotification
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
        return 'Thesis review submitted';
    }

    public function message(): string
    {
        return sprintf(
            '%s submitted a review for "%s" (%s).',
            $this->review->reviewer->name,
            $this->review->thesis->title,
            $this->review->decision?->label() ?? 'decision recorded',
        );
    }

    public function actionUrl(object $notifiable): string
    {
        if ($notifiable->isSupervisor()) {
            return route('supervisor.theses.show', $this->review->thesis);
        }

        return route('admin.theses.show', $this->review->thesis);
    }
}
