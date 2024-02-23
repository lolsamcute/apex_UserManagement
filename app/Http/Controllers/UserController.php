<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Add Log Facade

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Retrieve all users
        $users = User::all();

        // Return users as response
        return response()->json(['users' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        // Return validation error response if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check if the role exists
        $checkRole = Role::find($request->role_id);

        if (!$checkRole) {
            return response()->json(['error' => 'Role not found'], 422);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'roles' => $checkRole,
        ]);

        // Log user creation activity
        Log::info('New user created: ' . $user->email);

        // Return created user as response
        return response()->json(['user' => $user], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        // Return user details as response
        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        // Return validation error response if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check if the role exists
        $checkRole = Role::find($request->role_id);

        if (!$checkRole) {
            return response()->json(['error' => 'Role not found'], 422);
        }

        // Update user details
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'roles' => $checkRole,
        ]);

        // Log user update activity
        Log::info('User details updated: ' . $user->email);

        // Return updated user as response
        return response()->json(['user' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        // Delete the user
        $user->delete();

        // Log user deletion activity
        Log::info('User deleted: ' . $user->email);

        // Return success response
        return response()->json(null, 204);
    }
}
