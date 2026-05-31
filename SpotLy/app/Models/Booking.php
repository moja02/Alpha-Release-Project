<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // السماح بالإضافة في كل الحقول
    protected $guarded = [];

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'account_id');
    }

    public function parking()
    {
        return $this->belongsTo(Parking::class);
    }
}
