<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new UserRegistered($user)); // Dispatch the event

        return response()->json(['message' => 'User registered successfully']);
    }

    // Login a user and return the token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
       


        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = auth()->user()->createToken('Laravel')->accessToken;
            // dd($token);
            $user['token'] = $token;
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // Logout the user
    public function logout(Request $request)
    {
        $request->user()->token()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    // List all users
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
}
