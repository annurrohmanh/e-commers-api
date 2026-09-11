<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = config('app.frontend_url')
            . '/reset-password?token=' . $this->token;

        return (new MailMessage)
            ->subject('Reset Password')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Click the button below to reset your password. Link expires in 15 minutes.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, ignore this email.')
            ->salutation('Regrads, ADVNTURES');
    }
}