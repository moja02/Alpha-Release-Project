<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // السماح بالإدخال الجماعي لحساب الموظف البنكي
    protected $fillable = [
        'account_id',
        'bank_account_number',
    ];
}
