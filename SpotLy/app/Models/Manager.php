<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $table = 'managers';

    protected $fillable = [
        'account_id',
        'status'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function parkings()
    {
        return $this->hasMany(Parking::class, 'manager_id');
    }
}
