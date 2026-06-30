<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Milestone;
use Illuminate\Support\Collection;

class MilestoneDueSoonNotification extends PlatformNotification
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
        return 'Milestones due soon';
    }

    public function message(): string
    {
        $count = $this->milestones->count();

        return $count === 1
            ? sprintf('"%s" is due within 3 days.', $this->milestones->first()->title)
            : sprintf('You have %d milestones due within 3 days.', $count);
    }

    public function actionUrl(object $notifiable): string
    {
        return route('dashboard');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            ...parent::toArray($notifiable),
            'milestone_ids' => $this->milestones->pluck('id')->all(),
        ];
    }
}
