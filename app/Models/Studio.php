<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Studio extends Model
{
    protected $fillable = [
        'theater_id',
        'name',
        'seat_map',
    ];

    public function theater() {
        return $this->belongsTo(Theater::class);
    }

    public function schedule() {
        return $this->hasMany(Schedule::class);
    }

    public function seat() {
        return $this->hasMany(Seat::class);
    }

    public function seatDetail() {
        return $this->hasMany(SeatDetail::class);
    }

    protected static function booted()
    {
        static::deleting(function ($studio) {
            $studio->seat()->delete();
        });
    }
}
