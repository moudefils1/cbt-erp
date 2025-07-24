<?php

namespace App\Models;

use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InternItem extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'intern_id',
        'location_id',
        'employee_id',
        'start_date',
        'end_date',
        'duration',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => StateEnum::class,
    ];

    protected static $logName = 'departement de stagiaire';

    public function intern()
    {
        return $this->belongsTo(Intern::class)
            ->with('grade');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
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
}
