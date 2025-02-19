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
    public function __construct($user, $pdfPath)
    {
        $this->user = $user;
        $this->pdfPath = $pdfPath;
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
                    ->attach($this->pdfPath);
    }
}
