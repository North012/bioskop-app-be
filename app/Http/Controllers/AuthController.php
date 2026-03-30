<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function error()
    {
        return response()->json(['message' => 'token invalid or no token']);
    }

    // register
    public function register(Request $request) 
    {
        $validate = $request->validate([
            'name'  => 'required|string',
            'email'  => 'required|email|unique:users',
            'password'  => 'required|min:6',
        ]);

        $user = User::create([
            'name'  => $validate['name'],
            'email'     => $validate['email'],
            'password'  => Hash::make($validate['password']),
            'phone_number' => '-',
            'role'  => 'customer',
        ]);

        return response()->json(['message' => 'User registered successfully']);
    }

    public function login(Request $request)
    {
        $validate = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        $user = User::where('email', $validate['email'])->first();

        if (!$user || !Hash::check($validate['password'], $user->password)) {
            return response()->json(['message' => 'Email or password is incorrect'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'   => 'Login Successfully',
            'token'     => $token,
            'user'      => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
