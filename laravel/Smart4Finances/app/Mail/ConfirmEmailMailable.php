<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $url;

    public function __construct($user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('🚀 Confirmação de E-mail - Smart4Finances')
                    ->view('emails.confirm-email')
                    ->with([
                        'user' => $this->user,
                        'url'  => $this->url,
                    ]);
    }

}
