<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class PlatformNotification extends Notification
{
    use Queueable;

    abstract public function category(): NotificationCategory;

    abstract public function title(): string;

    abstract public function message(): string;

    abstract public function actionUrl(object $notifiable): string;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->deliveryChannelsFor($this->category());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'title' => $this->title(),
            'message' => $this->message(),
            'action_url' => $this->actionUrl($notifiable),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->message())
            ->action('View details', $this->actionUrl($notifiable));
    }
}
