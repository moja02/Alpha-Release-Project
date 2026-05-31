<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'accounts';
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
