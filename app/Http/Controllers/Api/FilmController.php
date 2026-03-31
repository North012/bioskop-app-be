<?php

namespace App\Http\Controllers\Api;

use App\Models\Film;
use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FilmController extends Controller
{
    public function index()
    {
        $film = Film::latest()->get();
        return new MasterResource(true, 'Data film berhasil ditampilkan', $film);
    }

    public function nowPlaying()
    {
        $films = Film::where('status', 'available')->get();
        return new MasterResource(true, 'Data film sedang tayang', $films); 
    }

    public function filmScheduleList($id)
    {
        $film = Film::with(['schedule.studio.theater'])->findOrFail($id);

        $schedules = $film->schedule
            ->groupBy('date')
            ->map(function ($scheduleByDate) {

                // Group schedule berdasarkan theater
                $theaters = $scheduleByDate
                    ->groupBy(fn($s) => $s->studio->theater->id)
                    ->map(function ($items) {

                        $theater = $items->first()->studio->theater;

                        return [
                            'theater_name' => $theater->name,
                            'theater_address' => $theater->address,
                            'times' => $items
                                ->sortBy(fn($item) => strtotime($item->time)) // Urutkan berdasarkan time
                                ->map(fn($item) => [
                                    'id'   => $item->id,
                                    'time' => $item->time,
                                ])
                            ->values(),
                        ];
                    })
                    ->values();

                return [
                    'date'     => $scheduleByDate->first()->date,
                    'theaters' => $theaters,
                ];
            })
            ->where('date', today()->toDateString())
            ->values();

        return response()->json([
            'id'          => $film->id,
            'title'       => $film->title,
            'description' => $film->description,
            'duration'    => $film->duration,
            'category'    => $film->category,
            'image'       => $film->image,
            'trailer'     => $film->trailer,
            'schedules'   => $schedules
        ]);
    }

    public function show(String $id)
    {
        $film = Film::findOrFail($id);
        return new MasterResource(true, 'Data Film:', $film);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title'         => 'required',
            'description'   => 'required',
            'date'          => 'required',
            'duration'      => 'required',
            'category'      => 'required',
            'status'        => 'required|in:available,unavailable',
            'image'         => 'required|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $image = $request->file('image');
        $imageName = $image->hashName();
        $image->storeAs('film', $imageName, 'public'); // => file disimpan di storage/app/public/film

        $film = Film::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'date'          => $request->date,
            'duration'      => $request->duration,
            'category'      => $request->category,
            'status'        => $request->status,
            'image'         => 'storage/film/' . $imageName,
            'trailer'       => $request->trailer ?? '-',
        ]);

        if ($film) {
            return new MasterResource(true, 'Data berhasil ditambahkan!', $film);
        } else {
            Storage::delete('public/film/' . $imageName);
            return response()->json(['error' => 'Gagal ditambahkan!'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $film = Film::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,jpg,png,svg,gif|max:2048',
            'status'  => 'nullable|in:available,unavailable',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->hashName();

            $image->storeAs('film', $imageName, 'public');
            Storage::disk('public')->delete('film/' . basename($film->image));

            $film->update([
                'title'         => $request->title ?? $film->title,
                'description'   => $request->description ?? $film->description,
                'date'          => $request->date ?? $film->date,
                'duration'      => $request->duration ?? $film->duration,
                'category'      => $request->category ?? $film->category,
                'status'        => $request->status ?? $film->status,
                'image'         => '/storage/film/' . $imageName,
                'trailer'       => $request->trailer ?? $film->trailer,
            ]);

        }else {

            $film->update([
                'title'         => $request->title ?? $film->title,
                'description'   => $request->description ?? $film->description,
                'date'          => $request->date ?? $film->date,
                'duration'      => $request->duration ?? $film->duration,
                'category'      => $request->category ?? $film->category,
                'status'        => $request->status ?? $film->status,
                'trailer'       => $request->trailer ?? $film->trailer,
            ]);
        }

        return new MasterResource(true, 'Data berhasil diubah!', $film);
    }

    public function updateStatus(Request $request, string $id)
    {
        $film = Film::findOrFail($id);
        
        if ($film['status'] == 'unavailable') {
            $film->update([
                'status'    => 'available'
            ]);

        } else {
            $film->update([
                'status'    => 'unavailable'
            ]);
        }
        return new MasterResource(true, 'Status berhasil diubah!', $film);

    }

    public function destroy(String $id)
    {
        $film = Film::findOrFail($id);

        if (!$film) {
            return new MasterResource(true, 'Data tidak ditemukan', null);
        }

        Storage::disk('public')->delete('film/' . basename($film->image));
        $film->delete();

        return new MasterResource(true, 'Data berhasi dihapus', null);
    }

    public function searchDate(Request $request, $id)
    {
        $selectedDate = $request->date ?? today()->toDateString();

        $film = Film::with(['schedule.studio.theater'])->findOrFail($id);

        $schedules = $film->schedule
            ->groupBy('date')
            ->map(function ($scheduleByDate) {

                $theaters = $scheduleByDate
                    ->groupBy(fn($s) => $s->studio->theater->id)
                    ->map(function ($items) {

                        $theater = $items->first()->studio->theater;

                        return [
                            'theater_name' => $theater->name,
                            'theater_address' => $theater->address,
                            'times' => $items
                                ->sortBy(fn($item) => strtotime($item->time))
                                ->map(fn($item) => [
                                    'id'   => $item->id,
                                    'time' => $item->time,
                                ])
                                ->values(),
                        ];
                    })
                    ->values();

                return [
                    'date'     => $scheduleByDate->first()->date,
                    'theaters' => $theaters,
                ];
            })
            ->where('date', $selectedDate) // 🔥 pakai tanggal dari request
            ->values();

        return response()->json([
            'id'          => $film->id,
            'title'       => $film->title,
            'description' => $film->description,
            'duration'    => $film->duration,
            'category'    => $film->category,
            'image'       => $film->image,
            'trailer'     => $film->trailer,
            'schedules'   => $schedules
        ]);
    }         
}
