<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\MasterResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index() 
    {
        $user = User::latest()->get();
        return new MasterResource(true, 'List user berhasil ditampilkan', $user);
    }

    public function authUser(Request $request)
    {
        $user = $request->user();
        return new MasterResource(true, 'Data User Login:', $user);
    }

    public function show(String $id)
    {
        $user = User::findOrFail($id);
        return new MasterResource(true, 'Data User: ', $user);
    }

    public function store(Request $request) 
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'required',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|',
            'phone_number'  => 'nullable|min:12',
            'role'          => 'nullable|in:admin,adminLocation,adminEvent,customer',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => $request->password,
            'phone_number'  => $request->phone_number,
            'role'          => $request->role,
        ]);

        return new MasterResource(true, 'Data berhasil ditambahkan', $user);
    }

    public function update(Request $request, string $id) 
    {
        $user = User::findOrFail($id);
        $oldEmail = $user->email;

        $validate = Validator::make($request->all(), [
            'name'          => 'nullable',
            'email'         => ['nullable', 'email', Rule::unique('users')->ignore($id)],
            'password'      => 'nullable|min:6',
            'phone_number'  => 'nullable|min:12',
            'role'          => 'nullable|in:admin,adminLocation,adminEvent,customer',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $user->update([
            'name'          => $request->name ?? $user->name,
            'email'         => $request->email ?? $user->email,
            'password'      => $request->filled('password') ? Hash::make($request->password) : $user->password,
            'phone_number'  => $request->phone_number ?? $user->phone_number,
            'role'          => $request->role ?? $user->role,
        ]);

        return new MasterResource(true, 'Berhasil diupdate', $user);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return new MasterResource (true, 'User tidak ditemukan', null);
        }

        $user->delete();
        return new MasterResource(true, 'Berhasil dihapus!', null);
    }
}
