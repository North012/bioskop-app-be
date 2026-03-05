<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'studio_id',
        'seat_number',
        'row',
        'column',
        'status'
    ];

    public function studio() {
        return $this->belongsTo(Studio::class);
    }

    public function bookingDetail() {
        return $this->hasMany(BookingDetail::class);
    }

    public function seatDetail() {
        return $this->hasMany(SeatDetail::class);
    }
}
