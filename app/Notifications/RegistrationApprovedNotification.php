<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApprovedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/user/login');

        return (new MailMessage)
            ->subject('Your Registration Has Been Approved — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your registration has been reviewed and approved by our admin team.')
            ->line('You can now log in to your account and start using the platform.')
            ->action('Login to Your Account', $loginUrl)
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Welcome aboard, ' . config('app.name') . ' Team');
    }
}
