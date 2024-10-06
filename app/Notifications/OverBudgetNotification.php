<?php

// app/Notifications/OverBudgetNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverBudgetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $category;
    protected $amountOver;

    public function __construct($category, $amountOver)
    {
        $this->category = $category;
        $this->amountOver = $amountOver;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // You can also use 'database' or other channels
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Over Budget Alert')
            ->greeting('Hello!')
            ->line("You have exceeded your budget for the category '{$this->category}' by M{$this->amountOver}.")
            ->action('View Budget', url('/budgets'))
            ->line('Please review your budget to make adjustments.');
    }
}
