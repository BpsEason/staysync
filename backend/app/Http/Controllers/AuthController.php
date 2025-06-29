<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Facades\Tenancy;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Get the current tenant ID from the tenancy context
            $tenantId = null;
            if (Tenancy::tenant()) {
                $tenantId = Tenancy::tenant()->id;
            }

            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign a default role to the new user within their tenant context
            if ($tenantId) {
                // Ensure the role exists for this tenant
                $guestRole = Role::where('name', 'guest_user')
                                 ->where('tenant_id', $tenantId)
                                 ->first();
                if ($guestRole) {
                    $user->assignRole($guestRole);
                } else {
                    // Fallback or create if guest_user role doesn't exist for this tenant
                    $newGuestRole = Role::create(['name' => 'guest_user', 'guard_name' => 'web', 'tenant_id' => $tenantId]);
                    $user->assignRole($newGuestRole);
                }
            } else {
                // For central users (if your app supports them outside tenancy)
                // You might assign a default central role here.
            }


            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle user login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Get the current tenant ID from the tenancy context
            $tenantId = null;
            if (Tenancy::tenant()) {
                $tenantId = Tenancy::tenant()->id;
            }

            // Find user within the current tenant scope
            $user = User::where('email', $request->email)
                        ->where('tenant_id', $tenantId) // Filter by tenant_id
                        ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful!',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Log the user out (revoke all tokens).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
