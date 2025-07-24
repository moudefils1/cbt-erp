<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveType extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'days',
        'is_paid',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    protected static $logName = 'type de congé';

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

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function employeeLeaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}
