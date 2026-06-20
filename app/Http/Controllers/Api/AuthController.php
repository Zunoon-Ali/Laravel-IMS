<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * User Login
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * User Registration
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Registration successful', 201);
    }

    /**
     * User Logout
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(): JsonResponse
    {
        return $this->successResponse(auth()->user(), 'Profile retrieved successfully');
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(\Illuminate\Http\Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->has('profile_image')) {
            $user->profile_image = $request->profile_image;
        }
        $user->save();

        return $this->successResponse($user, 'Profile updated successfully');
    }

    /**
     * Upload user profile image.
     */
    public function uploadProfileImage(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_profile_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Ensure public/uploads directory exists
            $path = public_path('uploads');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $file->move($path, $filename);
            $url = url('uploads/' . $filename);
            
            return $this->successResponse(['url' => $url], 'Profile image uploaded successfully');
        }

        return $this->errorResponse('No image file provided', 400);
    }
}
