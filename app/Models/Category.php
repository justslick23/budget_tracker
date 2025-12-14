<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',  // The name of the category
        'type',  // The type of category ('expense' or 'income')
        'user_id'
    ];

/*************  ✨ Windsurf Command ⭐  *************/
/*******  186188b1-9a47-4f21-99b6-d5e300f4e5b7  *******/
    public function user()
{
    return $this->belongsTo(User::class);
}

public function expense()
{
    return $this->hasMany(Expense::class);
}



}
