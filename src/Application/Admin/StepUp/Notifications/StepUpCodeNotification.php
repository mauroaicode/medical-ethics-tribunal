<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StepUpCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $action
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('step_up.email_subject'))
            ->view('emails.step-up.code', [
                'code' => $this->code,
                'action' => $this->action,
                'validityMinutes' => config('step-up.code.validity_minutes'),
            ]);
    }
}
