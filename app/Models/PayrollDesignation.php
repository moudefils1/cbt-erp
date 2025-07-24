<?php

namespace App\Models;

use App\Enums\OperationEnum;
use App\Enums\PartEnum;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PayrollDesignation extends Model
{
    use HasFactory;
    use HasUuid;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'part',
        'operation',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'part' => PartEnum::class,
        'operation' => OperationEnum::class,
    ];

    protected static $logName = 'désignations de paie';

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
}
