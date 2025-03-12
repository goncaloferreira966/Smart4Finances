<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendReport extends Mailable
{
    use Queueable, SerializesModels;

    public $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function build()
    {
        return $this->subject("Relatório Smart4Finances")
            ->view("emails.report")
            ->attach(storage_path("app/private/" . $this->path));
    }
}