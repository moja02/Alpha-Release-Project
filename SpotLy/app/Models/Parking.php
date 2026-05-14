<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location_park',
        'total_capacity',
        'available_capacity',
        'employee_id' 
    ];

    //  علاقة الساحة بالموظف المسؤول عنها
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}