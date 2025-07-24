<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalaryBonus extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'amount',
        'status',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    protected static $logName = 'prime de salaire';

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

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, '.', ',');
    }

//    public function setAmountAttribute($value)
//    {
//        $this->attributes['amount'] = Crypt::encryptString($value);
//    }
//
//    public function getAmountAttribute($value)
//    {
//        return Crypt::decryptString($value);
//    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
