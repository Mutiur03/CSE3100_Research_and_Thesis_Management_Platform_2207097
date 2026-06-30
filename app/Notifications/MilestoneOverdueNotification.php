<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Milestone;
use Illuminate\Support\Collection;

class MilestoneOverdueNotification extends PlatformNotification
{
    /**
     * @param  Collection<int, Milestone>  $milestones
     */
    public function __construct(
        public readonly Collection $milestones,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Milestones;
    }

    public function title(): string
    {
        return 'Overdue milestones';
    }

    public function message(): string
    {
        $count = $this->milestones->count();

        return $count === 1
            ? sprintf('"%s" is overdue.', $this->milestones->first()->title)
            : sprintf('You have %d overdue milestones.', $count);
    }

    public function actionUrl(object $notifiable): string
    {
        return route('dashboard');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable->prefersEmailFor($this->category())) {
            return [];
        }

        return ['mail'];
    }
}
