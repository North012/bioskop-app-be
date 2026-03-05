<?php

namespace App\Http\Controllers\Api;

use App\Models\Studio;
use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StudioController extends Controller
{
    public function index()
    {
        $studio = Studio::with('theater')->get();
        return new MasterResource(true, 'Seluruh data studio berhasil ditampilkan', $studio);
    }

    public function show(String $id)
    {
        $studio = Studio::with(['seat' => function($query) {
            $query->orderBy('row')->orderBy('column');
        }])->findOrFail($id);

        return new MasterResource(true, "Data studio dengan kursinya", $studio);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'theater_id'   => 'required|exists:theaters,id',
            'name'          => 'required',
            'seat_map'      => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $studio = Studio::create([
            'theater_id'    => $request->theater_id,
            'name'          => $request->name,
            'seat_map'      => $request->seat_map,
        ]);

        return new MasterResource(true, 'Data berhasil disimpan!', $studio);
    }

    public function update(Request $request, string $id)
    {
        $studio = Studio::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'theater_id'   => 'required|exists:theaters,id',
        ]);

        if ($validate->fails()) {
            return response()->json($request->errors(), 422);
        }

        $studio->update([
            'theater_id'    => $request->theater_id ?? $studio->theater_id,
            'name'          => $request->name ?? $studio->name,
            'seat_map'      => $request->seat_map ?? $studio->seat_map,
        ]);

        return new MasterResource(true, 'Data berhasil diupdate', $studio);
    }

    public function destroy(String $id)
    {
        $studio = Studio::findOrFail($id);

        if (!$studio) {
            return new MasterResource(true, 'Data tidak ditemukan', null);
        }

        $studio->delete();
        return new MasterResource(true, 'Data berhasil dihapus!', null);
    }
}
