<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Debt extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'amount',
        'borrowed_at',
        'reason',
        'is_paid',
        'paid_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'paid_at' => 'date',
    ];

    protected static $logName = 'prêts';

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

    //    public function items()
    //    {
    //        return $this->hasMany(DebtItem::class);
    //    }

//    public function setAmountAttribute($value)
//    {
//        $this->attributes['amount'] = Crypt::encryptString($value);
//    }
//
//    // Decrypt amount when reading
//    public function getAmountAttribute($value)
//    {
//        return Crypt::decryptString($value);
//    }
}
