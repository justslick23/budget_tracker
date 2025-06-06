<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $pdfPath
     */
    public function __construct($user, $pdfContent, $fileName)
    {
        $this->user = $user;
        $this->pdfContent = $pdfContent; 
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user_report')
                    ->with(['user' => $this->user])
                    ->subject('Your Monthly Budget Report')
                    ->attachData($this->pdfContent, $this->fileName, [
                        'mime' => 'application/pdf',
                    ]);    }
}
