<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theater extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'address',
    ];

    public function studio() {
        return $this->hasMany(Studio::class);
    }
    public function location() {
        return $this->belongsTo(Location::class);
    }
}
