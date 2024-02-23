<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Add Log Facade

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Return validation error response if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $data['user'] = $user;
        // Create token for the user
        $data['token'] = $user->createToken('ApexUserManagement')->accessToken;


        // Log registration activity
        Log::info('User registered successfully: ' . $user->email);

        // Return token as response
        return response()->json($data, 200);
    }

    /**
     * Authenticate the user and generate access token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Attempt user authentication
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            // Return unauthorized error if authentication fails
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        // Generate access token for the authenticated user
        $token = Auth::user()->createToken('ApexUserManagement')->accessToken;

        // Log login activity
        Log::info('User logged in: ' . Auth::user()->email);

        // Return token as response
        return response()->json(['token' => $token], 200);
    }

    /**
     * Logout the authenticated user (revoke the access token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the user's access token
        $request->user()->token()->revoke();

        // Log logout activity
        Log::info('User logged out: ' . $request->user()->email);

        // Return success message
        return response()->json(['message' => 'Successfully logged out']);
    }
}
