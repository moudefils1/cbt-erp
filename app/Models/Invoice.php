<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Invoice extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use softDeletes;

    protected $fillable = [
        'invoice_number',
        'amount',
        'invoice_status',
        'supplier_id',
        'receptionists',
        'invoice_description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receptionists' => 'json',
        'invoice_status' => InvoiceStatusEnum::class,
    ];

    protected static $logName = 'facture';

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
        $this->addMediaCollection('invoice_doc');
        $this->addMediaCollection('receipt_doc');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getReceptionistNamesAttribute()
    {
        $receptionistIds = $this->receptionists;

        return Employee::whereIn('id', $receptionistIds)
            ->get()
            ->map(function ($user) {
                return "{$user->name} {$user->surname}";
            })
            ->toArray();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(static::$logName);
    }

    // relation with invoice payments
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
