<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

class VerifyEmailNotification extends BaseVerifyEmail
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Explicitly construct the reset URL
        $resetUrl = $this->token;

        return (new MailMessage)
            ->subject('Verify Your Email')
            ->line('Please click the button below to verify your email address.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}

