<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'category_id', 'description', 'date', 'user_id'];
    protected $casts = [
        'date' => 'datetime', // Automatically casts date to a Carbon instance
    ];
    public function user()
{
    return $this->belongsTo(User::class);
}

// In App\Models\Expense.php

public function category()
{
    return $this->belongsTo(Category::class);
}


}
