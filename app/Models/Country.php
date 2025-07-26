<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'countries';

    protected $fillable = ['id', 'name', 'code'];

    public array $translatable = ['name',];

    protected $casts = ['name' => 'array',];

    //relations with employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'country_id', 'id');
    }
}
