<?php

// app/Notifications/ExpenseRecorded.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseRecorded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $category;

    /**
     * Create a new notification instance.
     *
     * @param float $amount The amount of the expense.
     * @param string $category The category of the expense.
     */
    public function __construct($amount, $category)
    {
        $this->amount = $amount; // The amount spent
        $this->category = $category; // The category of the expense
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // You can also use 'database' or other channels
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Expense Recorded') // Subject of the email
            ->greeting('Hello!') // Greeting line
            ->line("You have recorded an expense of M{$this->amount} under the category '{$this->category->name}', description: {$this->description}.") // Body message
            ->action('View Expenses', url('/expenses')) // Action button with URL
            ->line('Thank you for using our application!'); // Closing line
    }
}
