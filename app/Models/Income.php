<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

}
