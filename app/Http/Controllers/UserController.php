<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'tc' => 'required'
        ]);

        if (User::where('email', $request->email)->first()) {
            return response([
                'message' => 'Email already exists',
                'status' => 'failed'
            ], 200);
        }



        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tc' => json_decode($request->tc)
        ]);

        $token = $user->createToken($request->email)->plainTextToken;



        return response([
            'message' => 'Successfully registered',
            'status' => 'success',
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken($request->email)->plainTextToken;
            return response([
                'message' => 'Successfully logged in',
                'status' => 'success',
                'token' => $token
            ], 200);
        }

        return response([
            'message' => 'Invalid credentials',
            'status' => 'failed'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Successfully logged out',
            'status' => 'success'
        ], 200);
    }
    public function logged_user(Request $request)
    {
        $loggedUser = User::where('id', $request->user()->id)->first();
        return response([
            'user' => $loggedUser,
            'message' => 'Logged User data',
            'status' => 'success'
        ], 200);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:6',
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('id', $request->user()->id)->first();

        if ($user && Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            return response([
                'message' => 'Successfully changed password',
                'status' => 'success'
            ], 200);
        }

        return response([
            'message' => 'Invalid password',
            'status' => 'failed'
        ], 400);
    }
}