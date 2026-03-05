<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatDetail extends Model
{
    protected $fillable = [
        'seat_id',
        'schedule_id',
        'status',
    ];

    public function studio() {
        return $this->belongsTo(Studio::class);
    }

    public function seat() {
        return $this->belongsTo(Seat::class);
    }

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }
}
