<?php

namespace App\Models;

use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory;
    use LogsActivity;
    use softDeletes;

    protected $fillable = [
        'invoice_id',
        'name',
        'brand',
        'model',
        'serial_number',
        'mac_address',
        'plate_number',
        'chassis_number',
        'quantity',
        'is_available',
        'product_type_id',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'product_type_id' => ProductTypeEnum::class,
    ];

    protected static $logName = 'produit';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class)
            ->with('supplier');
    }

    // relationship with supplier model
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)
            ->with('invoices');
    }

    public function employeeProductItems(): HasMany
    {
        return $this->hasMany(EmployeeProductItem::class, 'product_id')
            ->with(['employee']);
    }

    /*public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_product_items')
            ->withPivot('quantity', 'is_active', 'description', 'created_by', 'updated_by')
            ->with('createdBy')
            ->withTimestamps();
    }*/

    /*protected static function booted()
    {
        static::deleting(function ($product) {
            EmployeeProduct::where('product_id', $product->id)
                ->update([
                    'deleted_by' => auth()->id(),
                    'deleted_at' => now(),
                ]);
        });
    }*/
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }
}
