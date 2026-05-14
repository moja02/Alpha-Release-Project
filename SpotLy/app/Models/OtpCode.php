<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    // تحديد الحقول المسموح تعبئتها برمجياً
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
    ];

    // تحويل صيغة الحقل الزمني تلقائياً
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}