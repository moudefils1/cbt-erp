<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EmployeeProductItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'employee_product_items';

    protected $fillable = [
        'employee_product_id',
        'employee_id',
        'product_id',
        'product_type_id',
        'quantity',
        'is_active',
        'description',
        'state',
        'state_quantity',
        'state_description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => ProductStatusEnum::class,
        'product_type_id' => ProductTypeEnum::class,
    ];

    protected static $logName = 'produit sortie';

    public function employeeProduct()
    {
        return $this->belongsTo(EmployeeProduct::class)
            ->with(['employee', 'product']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)
            ->with(['invoice', 'supplier']);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)
            ->with(['location', 'task']);
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
