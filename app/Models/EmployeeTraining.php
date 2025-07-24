<?php

namespace App\Models;

use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeTraining extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'training_id',
        //        'start_date',
        //        'end_date',
        //        'state',
        'created_by',
        'updated_by',
    ];

    //    protected $casts = [
    //        'start_date' => 'date',
    //        'end_date' => 'date',
    //        'state' => StateEnum::class,
    //    ];

    protected static $logName = 'formation de l\'employé';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
