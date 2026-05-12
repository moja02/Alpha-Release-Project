<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
