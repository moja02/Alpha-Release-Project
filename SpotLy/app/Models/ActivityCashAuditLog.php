<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCashAuditLog extends Model
{
    use HasFactory;

    protected $table = 'activity_cash_audit_logs';

    protected $fillable = [
        'employee_id',
        'parking_id',
        'operation_type',
        'plate_number',
        'cash_value',
        'driver_account_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function parking()
    {
        return $this->belongsTo(Parking::class, 'parking_id');
    }

    public function driver()
    {
        return $this->belongsTo(Account::class, 'driver_account_id');
    }
}
