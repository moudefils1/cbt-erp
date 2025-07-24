<?php

namespace App\Models;

use App\Enums\EchelonEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmployeeTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\GridCategoryEnum;
use App\Enums\MaritalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'matricule',
        'nni',
        'cnps_no',
        'name',
        'surname',
        'phone',
        'email',
        'gender',
        'country_id',
        'birth_place',
        'birth_date',
        'marital_status',
        'children_count',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'location_id',
        'employee_type_id',
        'grid_category_id',
        'echelon_id',
        'basic_salary',
        'task_id',
        'grade_id',
        'status',
        'status_start_date',
        'status_end_date',
        'status_comment',
        'on_leave',
        'on_training',
        'hiring_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'employee_type_id' => EmployeeTypeEnum::class,
        'status' => EmployeeStatusEnum::class,
        'hiring_date' => 'date',
        'end_date' => 'date',
        'status_start_date' => 'date',
        'status_end_date' => 'date',
        'birth_date' => 'date',
        'gender' => GenderEnum::class,
        'marital_status' => MaritalStatusEnum::class,
        'grid_category_id' => GridCategoryEnum::class,
        'echelon_id' => EchelonEnum::class,
    ];

    protected static $logName = 'personnel';

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

    public function employeeProductItems(): HasMany
    {
        return $this->hasMany(EmployeeProductItem::class, 'employee_id')
            ->with(['product', 'employee'])
            ->withCount('product');
    }

    public function getFullNameAttribute(): string
    {
        return $this->name.' '.$this->surname.' ('.$this->matricule.')';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('employee_documents');

        $this->addMediaCollection('employee_status_documents');

        $this->addMediaCollection('employee_status_item_documents');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function internItems(): HasMany
    {
        return $this->hasMany(InternItem::class, 'employee_id')
            ->with(['intern', 'location']);
    }

    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class, 'employee_id')
            ->with(['location', 'task']);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class)
            ->with(['leaveType', 'approvedBy', 'rejectedBy', 'createdBy', 'updatedBy']);
    }

    // relation with debt
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // relation with debt item
    public function debtItems(): HasMany
    {
        return $this->hasMany(DebtItem::class);
    }

    // relation with employee leave balance
    public function employeeLeaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    // chiifrement de basic_salary
//    public function setBasicSalaryAttribute($value)
//    {
//        $this->attributes['basic_salary'] = Crypt::encryptString($value);
//    }
//
//    public function getBasicSalaryAttribute($value)
//    {
//        return Crypt::decryptString($value);
//    }

    // relation with salary bonus
    public function salaryBonuses(): HasMany
    {
        return $this->hasMany(SalaryBonus::class);
    }

    // relation with salary deduction
    public function salaryDeductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    public function treatedSalaries(): HasMany
    {
        return $this->hasMany(TreatedSalary::class, 'employee_id');
    }

    // relation with country
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
