<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Recurring Expense Recorded</title>
</head>
<body>
    <h1>New Recurring Expense Recorded</h1>

    <p>Hello {{ $expense->user->name }},</p>

    <p>A recurring expense has been recorded automatically for you.</p>

    <ul>
        <li><strong>Amount:</strong> M {{ number_format($expense->amount, 2) }}</li>
        <li><strong>Category:</strong> {{ $expense->category->name }}</li>
        <li><strong>Description:</strong> {{ $expense->description }}</li>
        <li><strong>Date:</strong> {{ $expense->date->toFormattedDateString() }}</li>
    </ul>

    <p>Thanks for using our expense tracker!</p>
</body>
</html>
