<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

abstract class AuthController extends Controller
{

    abstract protected function getUserType(): string ;

    public function register(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $validatedData['type'] = $this->getUserType();
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Registred successfully', 'token' => $token], 201);
    }

    public function login(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8'
        ]);

        $user = User::where('email', $validatedData['email'])->firstOrFail();

        if(!Hash::check($validatedData['password'], $user->password)){
            return response()->json(['message' => 'Invalid Credintials']);
        };

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'logged in successfully', 'token' => $token], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function getProfile(Request $request){
        $user = $request->user();

        return response()->json(['user' => $user], 200);
    }

    public function getAccessToken(Request $request){
        $token = $request->user()->currentAccessToken();
        return response()->json(['token' => $token], 200);
    }
}
