<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EmployeeProduct extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'employee_products';

    protected $fillable = [
        'product_out_doc_number',
        'status',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => InvoiceStatusEnum::class,
    ];

    protected static $logName = 'bon de sortie';

    /*public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }*/

    public function employeeProductItems()
    {
        return $this->hasMany(EmployeeProductItem::class, 'employee_product_id')
            ->with(['product', 'employee']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_out_doc');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }
}
