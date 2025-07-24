<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'countries';

    protected $fillable = ['id', 'name', 'code'];

    protected $casts = [
        'name' => 'array',
    ];

    //relations with employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'country_id', 'id');
    }

    public function getNameFrAttribute()
    {
        return $this->name['fr'] ?? '';
    }

}
