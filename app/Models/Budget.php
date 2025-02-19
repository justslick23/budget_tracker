<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;


class Budget extends Model
{
    use HasFactory;
    protected $table = 'budgets';

 protected $fillable = [
        'user_id', 'category_id', 'amount', 'spent', 'year', 'month'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
{
    return $this->belongsTo(Category::class);
}

 // Decrypt values when accessed
 public function getAmountAttribute($value)
 {
     return (float) Crypt::decryptString($value);
 }

 public function getSpentAttribute($value)
 {
     return (float) Crypt::decryptString($value);
 }

 // Encrypt values before saving
 public function setAmountAttribute($value)
 {
     $this->attributes['amount'] = Crypt::encryptString((string) $value);
 }

 public function setSpentAttribute($value)
 {
     $this->attributes['spent'] = Crypt::encryptString((string) $value);
 }
    
}
