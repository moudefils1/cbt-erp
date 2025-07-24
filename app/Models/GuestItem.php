<?php

namespace App\Models;

use App\Enums\ApprovalEnum;
use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GuestItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'guest_id',
        'employee_id',
        'subject',
        'start_date',
        'end_date',
        'state',
        'approval',
        'approved_by',
        'approved_at',
        'postponed_by',
        'postponed_at',
        'postponed_reason',
        'canceled_by',
        'canceled_at',
        'cancel_reason',
        'resume',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_at' => 'date',
        'reported_at' => 'date',
        'canceled_at' => 'date',
        'state' => StateEnum::class,
        'approval' => ApprovalEnum::class,
    ];

    protected static $logName = 'planning de l\'invité';

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function postponedBy()
    {
        return $this->belongsTo(User::class, 'postponed_by');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by');
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
        $this->addMediaCollection('guest_items_documents');
    }
}
