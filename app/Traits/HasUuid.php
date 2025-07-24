<?php

namespace App\Traits;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            $random = rand(1000000, 9999999);

            while (self::where('uuid', $random)->exists()) {
                $random = rand(1000000, 9999999);
            }

            $model->uuid = $random;
        });
    }
}
