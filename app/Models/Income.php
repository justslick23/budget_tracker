<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Income extends Model
{
    use HasFactory;


    protected $fillable = [
        'source',
        'amount',
        'date',
        'user_id'
    ];


    protected $casts = [
        'date' => 'datetime', // Automatically cast to Carbon instance
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

   // Decrypt 'amount' when accessed
   public function getAmountAttribute($value)
   {
       return (float) Crypt::decryptString($value); // Convert decrypted value back to float
   }

   // Encrypt 'amount' before saving to the database
   public function setAmountAttribute($value)
   {
       $this->attributes['amount'] = Crypt::encryptString((string) $value); // Ensure it is saved as a string
   }

}
