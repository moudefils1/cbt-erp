<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DebtItem extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        // 'amount',
        'paid_amount',
        // 'remaining_amount',
        'paid_at',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static $logName = 'détail du prêt';

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

    //    public function debt()
    //    {
    //        return $this->belongsTo(Debt::class);
    //    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Chiffrement de paid_amount
//    public function setPaidAmountAttribute($value)
//    {
//        $this->attributes['paid_amount'] = Crypt::encryptString($value);
//    }
//
//    public function getPaidAmountAttribute($value)
//    {
//        return Crypt::decryptString($value);
//    }

    // Chiffrement de remaining_amount
    //    public function setRemainingAmountAttribute($value)
    //    {
    //        $this->attributes['remaining_amount'] = Crypt::encryptString($value);
    //    }
    //
    //    public function getRemainingAmountAttribute($value)
    //    {
    //        return Crypt::decryptString($value);
    //    }
}
