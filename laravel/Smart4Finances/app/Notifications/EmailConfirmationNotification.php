<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Mail\ConfirmEmailMailable;
use Illuminate\Support\Facades\Mail;

class EmailConfirmationNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('🚀 Confirmação de E-mail - Smart4Finances')
            ->view('emails.confirm-email', [
                'user' => $notifiable,
                'url'  => $url,
            ]);
    }

    protected function verificationUrl($notifiable)
    {
        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey()]
        );

        return $temporarySignedURL;
    }
}
