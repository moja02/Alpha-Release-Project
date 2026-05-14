<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    // السماح بتعبئة هذه الحقول برمجياً
    protected $fillable = [
        'user_id',
        'balance'
    ];
}
