<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Guest extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'name',
        'phone',
        'gender',
        'email',
        'city',
        'address',
        'company',
        'company_address',
        'company_phone',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gender' => GenderEnum::class,
        'status' => StatusEnum::class,
    ];

    protected static $logName = 'invité';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function guestItems()
    {
        return $this->hasMany(GuestItem::class);
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
        $this->addMediaCollection('guests_documents');
    }
}
