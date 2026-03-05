<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Payment;
use App\Models\SeatDetail;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Resource\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{

    public function detailPickOrder($id)
    {
        $schedule = Schedule::with([
            'studio.theater',
            'studio.seat.seatDetail' => function ($q) use ($id) {
                $q->where('schedule_id', $id);
            }
        ])->findOrFail($id);

        $seat = $schedule->studio->seat->map(function ($seat) {
            $status = $seat->status;

            if ($seat->seatDetail->isNotEmpty()) {
                $detailStatus = $seat->seatDetail->first()->status;

                if ($detailStatus === 'booked') {
                    $status = 'unavailable';
                } elseif ($detailStatus === 'process') {
                    $status = 'process';
                }
            }

            return [
                'seat_id'       => $seat->id,
                'seat_number'   => $seat->seat_number,
                'row'           => $seat->row,
                'column'        => $seat->column,
                'status'        => $status
            ];
        });

        return response()->json([
            'schedule_id'   => $schedule->id,
            'film_id'       => $schedule->film_id,
            'studio_id'     => $schedule->studio_id,
            'date'          => $schedule->date,
            'time'          => $schedule->time,
            'price'         => $schedule->price,

            "theater" => [
                'id'        => $schedule->studio->theater->id,
                'name'      => $schedule->studio->theater->name,
                'address'   =>$schedule->studio->theater->address,
                
                'studio' => [
                    [
                        'id'    => $schedule->studio->id,
                        'name'  => $schedule->studio->name,
                        'seats' => $seat
                    ]
                ]
            ]
        ]);
    } 

    public function updateSeatDetail(Request $request)
    {
        $request->validate([
            'seat_ids'      => 'required|array',
            'seat_ids.*'    => 'integer|exists:seats,id',
            'schedule_id'   => 'required|integer|exists:schedules,id',
        ]);

        $createdSeats = [];

        foreach ($request->seat_ids as $seatId) {
            $created = SeatDetail::create([
                'schedule_id'   => $request->schedule_id,
                'seat_id'       => $seatId,
                'status'    => 'process',
            ]);

            $createdSeats[] = $created;
        }

        return response()->json([
            'message' => 'Berhasil!',
            'data' => $createdSeats
        ], 201);
    }

    public function paymentDetail($schedule_id) {
        $schedule = Schedule::with([
            'studio.theater',
            'film'
        ])->findOrFail($schedule_id);

        return response()->json([
            'message'   => 'Success',
            'data'      => $schedule
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'schedule_id'       => 'required|exists:schedules,id',
            'payment_method'    => 'required|in:cash,qris,transfer',
            'seat_detail_id'    => 'required|array|min:1',
            'seat_detail_id.*'  => 'required|exists:seat_details,id',
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $schedule = Schedule::findOrFail($request->schedule_id);
            $seatDetails = SeatDetail::whereIn('id', $request->seat_detail_id)->get();

            // totalPrice = harga per kursi * jumlah kursi
            $totalPrice = $schedule->price * $seatDetails->count();

            $booking = Booking::create([
                'user_id'      => $user->id,
                'schedule_id'  => $request->schedule_id,
                'booking_time' => now(),
                'total_price'  => $totalPrice,
                'status'       => 'success',
            ]);

            foreach ($seatDetails as $sd) {
                $sd->update(['status' => 'booked']);

                BookingDetail::create([
                    'booking_id'    => $booking->id,
                    'seat_id'       => $sd->seat_id,
                    'price'         => $schedule->price,
                ]);
            }

            Payment::create([
                'booking_id'     => $booking->id,
                'payment_time'   => now(),
                'payment_method' => $request->payment_method,
                'amount'         => $totalPrice,
                'status'         => 'paid',
            ]);

            DB::commit();

            return response()->json([
                'message'    => 'Booking Berhasil',
                'booking_id' => $booking->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
