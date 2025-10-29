<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
