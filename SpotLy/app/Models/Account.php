<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    // السماح بالإدخال الجماعي لحقول الحساب الأساسية
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    // إخفاء كلمة المرور عند إرجاع البيانات بصيغة JSON
    protected $hidden = [
        'password',
    ];
    
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
