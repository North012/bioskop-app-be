<?php

namespace App\Http\Controllers\Api;

use App\Models\Theater;
use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TheaterController extends Controller
{
    public function index() {
        $theater = Theater::with('location')->get();
        return new MasterResource(true, 'List data berhasil diambil!', $theater);
    }

    public function show(String $id) 
    {
        $theater = Theater::with('location')->findOrFail($id);
        return new MasterResource(true, 'Data Theater: ', $theater);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'location_id'   => 'required|exists:locations,id',
            'name'          => 'required',
            'address'       => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->erros(), 422);
        }

        $theater = Theater::create([
            'location_id'   => $request->location_id,
            'name'          => $request->name,
            'address'       => $request->address,
        ]);

        return new MasterResource(true, 'Data berhasil ditambahkan!', $theater);
    }

    public function update(Request $request, string $id)
    {
        $theater = Theater::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
        ]);

        if ($validate->fails()) {
            return response()->json($request->errors(), 422);
        }

        $theater->update([
            'location_id'   => $request->location_id ?? $theater->location_id,
            'name'          => $request->name ?? $theater->name,
            'address'       => $request->address ?? $theater->address,
        ]);

        return new MasterResource(true, 'Data berhasil diupdate!', $theater);
    }

    public function destroy(String $id)
    {
        $theater = Theater::findOrFail($id);
        if (!$theater) {
            return new MasterResource(true, 'Data tidak ditemukan', null);
        }

        $theater->delete();
        return new MasterResource(true, 'Data berhasil dihapus!', null);
    }

}
