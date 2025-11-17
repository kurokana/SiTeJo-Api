<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Create new user (admin only - for dosen/admin creation)
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nim_nip' => 'required|string|unique:users',
            'role' => 'required|in:dosen,admin',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nim_nip.required' => 'NPM/NIP is required',
            'nim_nip.unique' => 'NPM/NIP already exists. Each NPM must be unique.',
            'email.unique' => 'Email already exists.',
            'role.in' => 'Only dosen and admin roles can be created here. Students must register publicly.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nim_nip' => $request->nim_nip,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Get all users (admin only)
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users
        ]);
    }

    /**
     * Get single user by ID
     */
    public function show(Request $request, $id)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => $user
        ]);
    }

    /**
     * Update user (admin only)
     */
    public function update(Request $request, $id)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nim_nip' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|required|in:mahasiswa,dosen,admin',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ], [
            'nim_nip.unique' => 'NPM/NIP already exists. Each NPM must be unique.',
            'email.unique' => 'Email already exists.',
        ]);

        // Update only provided fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('nim_nip')) {
            $user->nim_nip = $request->nim_nip;
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user (admin only)
     */
    public function destroy(Request $request, $id)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent deleting self
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
