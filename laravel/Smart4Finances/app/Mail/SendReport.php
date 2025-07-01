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
        $filePath = storage_path("app/" . $this->path);
        
        return $this->subject("Relatório Smart4Finances")
            ->view("emails.report")
            ->attach($filePath, [
                'as' => 'Smart4Finances_Relatório_Financeiro.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}