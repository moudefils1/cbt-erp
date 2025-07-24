<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Grade extends Model
{
    use HasFactory;
    use LogsActivity;
    use softDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static $logName = 'niveau';

    public function interns()
    {
        return $this->hasMany(Intern::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
