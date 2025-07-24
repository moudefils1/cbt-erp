<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\InternshipTypeEnum;
use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Intern extends Model implements HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'university',
        'department',
        'grade_id',
        'internship_start_date',
        'internship_end_date',
        'internship_duration',
        'internship_type',
        'status',
        'gender',
        'employee_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'internship_type' => InternshipTypeEnum::class,
        'status' => StateEnum::class,
        'internship_start_date' => 'date',
        'internship_end_date' => 'date',
        'gender' => GenderEnum::class,
    ];

    protected static $logName = 'stagiaire';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('internship_start_document')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            });

        $this->addMediaCollection('internship_end_report')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            });

        $this->addMediaCollection('internship_end_certificate')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            });

        $this->addMediaCollection('attachments')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            });

        $this->addMediaCollection('images')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'image/jpeg' || $file->mimeType === 'image/png';
            });

    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    //    public function employee()
    //    {
    //        return $this->belongsTo(Employee::class, 'employee_id');
    //    }

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

    public function internItems(): HasMany
    {
        return $this->hasMany(InternItem::class);
    }
}
