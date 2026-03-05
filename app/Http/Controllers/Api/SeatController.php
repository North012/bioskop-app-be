<?php

namespace App\Http\Controllers\Api;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    public function index()
    {
        $seat = Seat::latest()->get();
        return new MasterResource(true, 'Berhasil menampilkan keseluruhan data', $seat);
    }

    public function show(string $id)
    {
        $seat = Seat::findOrFail($id);
        return new MasterResource(true, 'Data Seat:', $seat);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'studio_id' => 'required|exists:studios,id',
            'rows'      => 'required|integer|min:1',
            'columns'   => 'required|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $studioId = $request->studio_id;
        $rows     = $request->rows;
        $columns  = $request->columns;

        $seats = [];

        for ($i = 0; $i < $rows; $i++) { 
            $rowLetter = chr(65 + $i);
            for ($j = 1; $j <= $columns; $j++) { 
                $seats[] = Seat::create([
                    'studio_id'   => $studioId,
                    'seat_number' => $rowLetter . $j,
                    'row'         => $rowLetter,
                    'column'      => $j,
                    'status'      => 'available'
                ]);
            }
        }

        return new MasterResource(true, 'Data berhasil disimpan', $seats);

    }

    public function update(Request $request)
    {
        // Validasi request
        $validate = Validator::make($request->all(), [
            'studio_id' => 'required|exists:studios,id',
            'status'    => 'required|in:empty,unavailable,available', // status yang valid
            'seat'      => 'required|array|min:1',
            'seat.*'    => 'string', // setiap seat adalah string, misal "A2", "A3"
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        // Ambil data seat berdasarkan studio_id dan seat_number yang dikirim
        $seatsToUpdate = Seat::where('studio_id', $request->studio_id)
                            ->whereIn('seat_number', $request->seat)
                            ->get();

        if ($seatsToUpdate->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada seat yang ditemukan untuk studio ini'
            ], 404);
        }

        // Update status untuk setiap seat
        foreach ($seatsToUpdate as $seat) {
            $seat->status = $request->status;
            $seat->save();
        }

        return new MasterResource(true, 'Status seat berhasil diupdate', $seatsToUpdate);
    }

    public function destroy(string $id)
    {
        $seat = Seat::findOrFail($id);
        if (!$seat) {
            return new MasterResource(true, 'Data tidak ditemukan', null);
        }

        $seat->delete();
        return new MasterResource(true, 'Data berhasil dihapus', null);
    }
}
