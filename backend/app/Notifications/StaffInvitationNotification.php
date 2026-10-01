<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a staff invitation link carrying the single-use token (decisions.md
 * D-10). The plaintext token is embedded only in this email and is never
 * stored; only its SHA-256 hash is persisted. The link opens the approved SPA
 * acceptance screen (hash route).
 */
class StaffInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $role,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim((string) config('app.url'), '/')
            .'/#/accept-invitation?'.http_build_query(['token' => $this->token]);

        $roleLabel = $this->role === 'admin' ? 'Super Admin' : 'Analyst';

        return (new MailMessage)
            ->subject('You have been invited to Taleed Procurement')
            ->greeting('You have been invited')
            ->line("You have been invited to join the Taleed Procurement team as a {$roleLabel}.")
            ->action('Accept the invitation', $url)
            ->line('This invitation expires in 72 hours and can be used once.')
            ->line('If you were not expecting this invitation, you can ignore this email.');
    }
}
