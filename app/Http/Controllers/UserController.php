<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function store(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);
      
        if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
           
            // Création de l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
          
            $accessToken = $user->createToken('access_token')->plainTextToken;
            $refreshToken = Str::random(64);
            $user->update(['refresh_token' => $refreshToken, 'refresh_token_expiration' => Carbon::now()->addDays(30)]);
            return response()->json(['user' => $user,
                                     'access_token' => $accessToken,
                                     'refresh_token' => $refreshToken], 201);

        
    }
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $user = User::where('refresh_token', $request->refresh_token)
                    ->where('refresh_token_expiration', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

      
        $user->tokens()->delete();
        $newAccessToken = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
        ]);
    }
}
