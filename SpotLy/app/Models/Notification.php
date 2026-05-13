<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // السماح بحفظ بيانات الإشعارات الفورية
    protected $fillable = [
        'user_id',
        'message',
        'type',
    ];
}
