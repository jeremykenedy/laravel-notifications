<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A flexible, reusable notification class with type support.
 *
 * Types: info, success, warning, danger, system
 *
 * Usage:
 *   $user->notify(new AppNotification(
 *       title: 'Invoice Ready',
 *       message: 'Invoice #1234 has been generated.',
 *       type: 'success',
 *       actionUrl: '/invoices/1234',
 *       actionText: 'View Invoice',
 *   ));
 *
 * Or via the NotificationService:
 *   app(NotificationService::class)->send($user, 'Title', 'Message', 'info');
 */
class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $type = 'info',
        protected ?string $actionUrl = null,
        protected ?string $actionText = null,
        protected bool $sendEmail = false,
        protected ?string $icon = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject($this->title)
            ->line($this->message);

        if ($this->actionUrl) {
            $mail->action($this->actionText ?? 'View', $this->actionUrl);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $data = [
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => $this->type,
        ];

        if ($this->icon) {
            $data['icon'] = $this->icon;
        }

        if ($this->actionUrl) {
            $data['action_url'] = $this->actionUrl;
            $data['action_text'] = $this->actionText ?? 'View';
        }

        return $data;
    }
}
