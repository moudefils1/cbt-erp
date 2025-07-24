<?php

namespace App\Models;

use App\Enums\AbsenceStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Absence extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'is_present',
        'status',
    ];

    protected $casts = [
        'date' => 'date:d/m/Y',
        'is_present' => 'boolean',
        'status' => AbsenceStatusEnum::class,
    ];

    protected static $logName = 'absences';

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
}
