<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TreatedSalary extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'treatment_date',
        'start_date',
        'end_date',
        'total_working_days',
        'actual_working_days',
        'total_working_hours',
        'actual_working_hours',
        'hourly_rate',
        'base_salary',
        'total_bonuses',
        'total_deductions',
        'final_salary',
        'bonus_details',
        'deduction_details',
        'notes',
        'is_paid',
        'paid_at',
        'created_by',
        'updated_by',
        'total_recoveries',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_at' => 'date',
        'is_paid' => 'boolean',
        'bonus_details' => 'array',
        'deduction_details' => 'array',
        'hourly_rate' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'total_bonuses' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'final_salary' => 'decimal:2',
    ];

    protected static $logName = 'traitement de salaire';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function salaryBonuses()
    {
        return $this->hasManyThrough(
            SalaryBonus::class,
            Employee::class,
            'id', // Foreign key on Employee table...
            'employee_id', // Foreign key on SalaryBonus table...
            'employee_id', // Local key on TraitedSalary table...
            'id' // Local key on Employee table...
        );
    }

    // related to Absence model
    public function absences()
    {
        return $this->hasManyThrough(
            Absence::class,
            Employee::class,
            'id', // Foreign key on Employee table...
            'employee_id', // Foreign key on Absence table...
            'employee_id', // Local key on TreatedSalary table...
            'id' // Local key on Employee table...
        );
    }
}
