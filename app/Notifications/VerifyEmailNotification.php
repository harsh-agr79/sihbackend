<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

class VerifyEmailNotification extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $type = $notifiable instanceof \App\Models\Student ? 'student' : 'mentor';
        $verifyUrl = config('app.frontend_url') . "/verify-email?type={$type}&email=" . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $verifyUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}

