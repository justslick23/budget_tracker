<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncomeRecorded extends Notification
{
    use Queueable;

    protected $amount;
    protected $source;

    public function __construct($amount, $source)
    {
        $this->amount = $amount;
        $this->source = $source;
    }

    public function via($notifiable)
    {
        return ['mail']; // or ['database'] if you're also storing it in the database
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Income Recorded')
            ->line("An income of M{$this->amount} from {$this->source} has been recorded.")
            ->action('View Incomes', url('/incomes'))
            ->line('Thank you for using our application!');
    }

    // Additional methods (toArray, etc.) can be added here
}
