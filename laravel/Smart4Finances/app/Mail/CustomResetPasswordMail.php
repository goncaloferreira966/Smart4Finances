<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $user;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->url = 'http://localhost:5173/reset-password?token=' . $token . '&email=' . urlencode($user->email);
    }

    public function build()
    {
        return $this->subject('🔐 Recuperação de Password - Smart4Finances')
                    ->view('emails.custom-reset-password');
    }
}

