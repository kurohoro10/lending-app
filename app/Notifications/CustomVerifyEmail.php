<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            // ->markdown('notifications.verify-email')
            // ->greeting('Hello!')
            // ->line('Thank you for applying for a loan at ZYA Capital, we are your trusted lender for commercial loans.')
            // ->line('Please click the button below to verify your email address.')
            // ->action('Verify Email Address', $this->verificationUrl($notifiable))
            // ->line('If you did not create an account, no further action is required.')
            // ->line('------------------------------------')
            // ->line('Kind regards,')
            // ->line('')
            // ->line('ZYA Capital Pty Ltd ABN: 55695692052')
            // ->line('')
            // ->line('www.zyacapital.com.au');
            // ->line('')
            // ->line('[Signature image placeholder]')
            // ->line('(JC will provide the signature image later, so leave this placeholder.)')
            // ->line('------------------------------------');
            ->view('notifications.verify-email-html', [
                'verificationUrl' => $this->verificationUrl($notifiable),
            ]);
    }
}
