<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // User Registration
    public function register(Request $request)
    {
        try {
            $fields = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Register Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during registration.'], 500);
        }
    }

    // User Login
    public function login(Request $request)
    {
        try {
            $fields = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $fields['email'])->first();

            if (!$user || !Hash::check($fields['password'], $user->password)) {
                return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during login.'], 500);
        }
    }

    // User Logout
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Logged out successfully.'], 200);
        } catch (\Throwable $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during logout.'], 500);
        }
    }
}
