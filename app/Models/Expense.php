<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'category_id', 'description', 'date', 'user_id'];
    protected $casts = [
        'date' => 'datetime',
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

public function getAmountAttribute($value)
{
    return (float) Crypt::decryptString($value); // Convert decrypted value back to float
}
public function setAmountAttribute($value)
{
    $this->attributes['amount'] = Crypt::encryptString((string) $value); // Ensure it is saved as a string
}

}
