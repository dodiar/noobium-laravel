<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\SignInRequest;

class AuthController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'picture' => env('AVATAR_GENERATOR_URL') . $validated['name'],
        ]);

        $token = auth()->login($user);

        if (!$token)
        {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'status'=> 'Error',
                    'message' => 'Cannot add user.'
                ],
                'data' => [],
            ], 500);
        }
        
        return response()->json([
            'meta' => [
                    'code' => 200,
                    'status'=> 'success',
                    'message' => 'User created successfully.'
                ],
                'data' => [
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'picture' => $user->picture,
                    ],
                    'access token' => [
                        'token' => $token,
                        'type' => 'Bearer',
                        'expires_in' => strtotime('+' . auth()->factory()->getTTL() . ' minutes'),
                    ]
                ],
        ]);
    }

    public function signIn(SignInRequest $request) 
    {

        $token = auth()->attempt($request->validated());

        if (!$token)
        {
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Incorrect email or password',
                ],
                'data' => [],
            ], 401);
        }

        $user = auth()->user();
        // dd($user);

        return response()->json([
            'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Signed in successfully',
                ],
                'data' => [
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'picture' => $user->picture,
                    ],
                    'access token' => [
                        'token' => $token,
                        'type' => 'Bearer',
                        'expires_in' => strtotime('+' . auth()->factory()->getTTL() . ' minutes'),
                    ]
                ],
        ]);
    }

    public function refresh()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'User not authenticated',
                ],
                'data' => [],
            ], 401);
        }

        $token = auth()->fromUser($user);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Token refreshed successfully.',
            ],
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'picture' => $user->picture,
                ],
                'access_token' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires_in' => strtotime('+' . auth()->factory()->getTTL() . ' minutes'),
                ]
            ],
        ]);
    }
}