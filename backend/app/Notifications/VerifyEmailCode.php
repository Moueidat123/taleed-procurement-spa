<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails the six-digit email-verification code (decisions.md D-30).
 *
 * The code is passed in and rendered once; it is never stored in plaintext.
 * Sent synchronously (not queued) so registration can confirm delivery, and so
 * local Mailpit shows it immediately.
 */
class VerifyEmailCode extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Taleed Procurement verification code')
            ->greeting('Verify your email')
            ->line('Enter this six-digit code to verify your email address:')
            ->line('**'.$this->code.'**')
            ->line('The code expires in 15 minutes. If you did not request it, you can ignore this email.');
    }
}
