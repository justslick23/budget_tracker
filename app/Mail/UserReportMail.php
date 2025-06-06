<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $pdfContent;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $pdfContent
     * @param $fileName
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
                    ]);
    }
}
