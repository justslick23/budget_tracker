<?php

namespace App\Jobs;

use App\Mail\MonthlyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $pdfPath;

    public function __construct($user, $pdfPath)
    {
        $this->user = $user;
        $this->pdfPath = $pdfPath;
    }

    public function handle()
    {
        Mail::send('emails.user_report', ['user' => $this->user], function ($message) {
            $message->to($this->user->email)
                    ->subject('Your Monthly Budget Report')
                    ->attach($this->pdfPath);
        });
    }
}
