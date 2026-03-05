<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'booking_time',
        'total_price',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }

    public function bookingDetail() {
        return $this->hasMany(BookingDetail::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }
}
