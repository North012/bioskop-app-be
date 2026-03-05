<?php

namespace App\Http\Controllers\Api;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index()
    {
        $location = Location::latest()->get();
        return new MasterResource(true, 'List Lokasi berhasil ditampilkan!', $location);
    }

    public function show(String $id)
    {
        $location = Location::findOrFail($id);
        return new MasterResource(true, 'Data Lokasi: ', $location);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $location = Location::create([
            'name'      => $request->name,
        ]);

        return new MasterResource(true, 'Berhasil ditambahkan', $location);
    }

    public function update(Request $request, String $id)
    {
        $location = Location::findOrFail($id);

        $location->update([
            'name'      => $request->name ?? $location->name,
        ]);

        return new MasterResource(true, 'Berhasil diubah!', $location);
    }

    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);

        if (!$location) {
            return new MasterResource(true, 'Data tidak ditemukan!', null);
        }

        $location->delete();
        return new MasterResource(true, 'Data berhasil dihapus!', null);
    }
}
