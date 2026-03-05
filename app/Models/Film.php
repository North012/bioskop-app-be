<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $fillable = [
       'title',
       'description',
       'date',
       'duration',
       'category',
       'trailer',
       'status',
       'image',
    ];

    public function schedule() {
        return $this->hasMany(Schedule::class);
    }

    public function getImageAttribute($value)
    {
        return asset($value);
    }
}
