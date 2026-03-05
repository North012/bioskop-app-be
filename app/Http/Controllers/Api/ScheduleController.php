<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use App\Models\Theater;
use App\Models\Studio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedule = Theater::get();
        return new MasterResource(true, 'Seluruh Data Bioskop', $schedule);
    }

    public function show(String $id)
    {
        $studios = Studio::with(['schedule.film'])
            ->where('theater_id', $id)
            ->get()
            ->map(function ($studio) {

                // Group schedules by date
                $grouped = $studio->schedule->groupBy(function ($item) {
                    return $item->date;
                });

                // Reformat
                $schedule = $grouped->map(function ($items, $date) {

                    // Group by film title
                    $films = $items->groupBy('film.title')->map(function ($filmItems, $title) {

                        return [
                            "title" => $title,

                            // id pertama dari film itu (opsional)
                            "id"    => $filmItems->first()->id,

                            // times masing-masing punya id schedule sendiri
                            "times" => $filmItems->map(function ($item) {
                                return [
                                    "id"   => $item->id,
                                    "time" => $item->time,
                                ];
                            })->values()
                        ];
                    })->values();

                    return [
                        "date" => $date,
                        "films" => $films,
                    ];
                })->values();

                return [
                    "studio_id" => $studio->id,
                    "studio_name" => $studio->name,
                    "theater_id" => $studio->theater_id,
                    "schedules" => $schedule
                ];
            });

        return response()->json($studios);
    }

    public function showEdit(string $id) {
        $schedule = Schedule::findOrFail($id);
        return new MasterResource(true, 'Detail data schedule', $schedule);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'film_id'       => 'required|exists:films,id',
            'studio_id'     => 'required|exists:studios,id',
            'date'          => 'required',
            'time'          => 'required',
            'price'         => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $schedule = Schedule::create([
            'film_id'       => $request->film_id,
            'studio_id'     => $request->studio_id,
            'date'          => $request->date,
            'time'          => $request->time,
            'price'         => $request->price,
        ]);

        return new MasterResource(true, 'Data berhasil ditambahkan', $schedule);
    }

    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'film_id'       => 'nullable|exists:films,id',
            'studio_id'     => 'nullable|exists:studios,id',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $schedule->update([
            'film_id'       => $request->film_id ?? $schedule->film_id,
            'studio_id'     => $request->studio_id ?? $schedule->studio_id,
            'date'          => $request->date ?? $schedule->date,
            'time'          => $request->time ?? $schedule->time,
            'price'         => $request->price ?? $schedule->price,
        ]);

        return new MasterResource(true, 'Data berhasill diubah', $schedule);
    }

    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        if (!$schedule) {
            return new MasterResource(true, 'Data tidak ditemukan', null);
        }

        $schedule->delete();
        return new MasterResource(true, 'Data berhasil dihapus', null);
    }
}
