<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Seat;
use App\Models\SeatDetail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resource\MasterResource;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function detailTransaction($id)
    {
        $booking = Booking::with([
            'schedule.film',
            'schedule.studio.theater',
            'bookingDetail.seat.seatDetail',
            'payment'
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'message'   => 'Success',
            'data'      => $booking,
        ]);
    }

    public function activeTicket()
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $booking = Booking::select('bookings.*')
            ->with([
                'schedule.film',
                'schedule.studio.theater',
            ])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->where('bookings.user_id', $userId)
            ->whereDate('schedules.date', '>=', $today)
            ->orderBy('schedules.date', 'asc')
            ->get();

        if ($booking->isEmpty()) {
            return response()->json([
                'message' => "Don't have active ticket"
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data'    => $booking,
        ]);
    }   

    public function nonActiveTicket()
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $booking = Booking::select('bookings.*')
            ->with([
                'schedule.film',
                'schedule.studio.theater',
            ])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->where('bookings.user_id', $userId)
            ->whereDate('schedules.date', '<', $today)
            ->orderBy('schedules.date', 'asc')
            ->get();

        if ($booking->isEmpty()) {
            return response()->json([
                'message' => "Don't have active ticket"
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data'    => $booking,
        ]);
    }   
}
