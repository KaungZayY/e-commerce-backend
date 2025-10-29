<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string',
            'email' => 'required|email|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name' => $req['name'],
            'email' => $req['email'],
            'user_role_id' => 2, // customer
            'password' => Hash::make($req['password'])
        ]);

        $token = $user->createToken('main')->plainTextToken;
        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $req)
    {
        $cred = $req->validate([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required',
            'remember' => 'boolean'
        ]);

        $remember = $cred['remember'] ?? false;
        unset($cred['remember']);

        if (!Auth::attempt($cred, $remember)) {
            return response([
                'error' => 'The provided credentials are not correct'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('main')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $req)
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response([
            'message' => 'successful',
        ], 200);
    }
}
