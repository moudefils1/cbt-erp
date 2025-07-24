<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EmployeePosition extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'location_id',
        'task_id',
        'position_start_date',
        'position_end_date',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'position_start_date' => 'datetime',
        'position_end_date' => 'datetime',
        'status' => StatusEnum::class,
    ];

    protected static $logName = 'poste occupée';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('employee_position_documents');
    }
}
