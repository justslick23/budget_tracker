<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Expense;

class RecurringExpenseCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expense;

    /**
     * Create a new message instance.
     */
    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('New Recurring Expense Recorded')
            ->markdown('emails.recurring_expense.created')
            ->with([
                'expense' => $this->expense,
            ]);
    }
}
