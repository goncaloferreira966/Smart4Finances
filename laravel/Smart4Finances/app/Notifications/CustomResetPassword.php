<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
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
        $url = 'http://localhost:5173/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('🔐 Recuperação de Password - Smart4Finances')
            ->greeting('Olá ' . $notifiable->name . ',')
            ->line('Recebemos um pedido para redefinir a sua password na Smart4Finances.')
            ->action('Redefinir Password', $url)
            ->line('Se não foste tu quem fez este pedido, podes ignorar este e-mail.')
            ->salutation('Cumprimentos, Equipa Smart4Finances');
    }
}
