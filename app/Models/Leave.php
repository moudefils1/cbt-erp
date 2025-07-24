<?php

namespace App\Models;

use App\Enums\LeaveEnum;
use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Leave extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'employee_leave_balance_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'state',
        'used_days',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'date',
        'rejected_at' => 'date',
        'status' => LeaveEnum::class,
        'state' => StateEnum::class,
    ];

    protected static $logName = 'congé';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function employeeLeaveBalance()
    {
        return $this->belongsTo(EmployeeLeaveBalance::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
