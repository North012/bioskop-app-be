<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'film_id',
        'studio_id',
        'date',
        'time',
        'price',
    ];

    public function film() {
        return $this->belongsTo(Film::class);
    }

    public function studio() {
        return $this->belongsTo(Studio::class);
    }

    public function booking() {
        return $this->hasMany(Booking::class);
    }

    public function seatDetail() {
        return $this->hasMany(SeatDetail::class);
    }

    public function getTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('H.i');
    }

}
