<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = config('app.frontend_url')
            . '/verify-email/' . $this->token;

        return (new MailMessage)
            ->subject('Verification Your Email')
            ->line('Click the button below to reset verify your email. Link expires in 1 hours.')
            ->action('Verify Your Email', $url)
            ->line('If you did not request a verification email, ignore this email.')
            ->salutation('Regrads, ADVNTURES');
    }
}