<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class GoogleAuthController extends Controller
{
    public function signIn(Request $request)
    {
        $request = $request->json()->all();

        $tokenParts = explode('.', $request['token']);

        $tokenPayload = base64_decode($tokenParts[1]);

        $jwtPayload = json_decode($tokenPayload, true);

        if ($jwtPayload)
        {
            return response()->json([
                'meta' => [
                    "code" => 422,
                    'status' => 'error',
                    "message" => 'Token invalid',
                ],
                'data' => [],
            ],422);
        }

        $findUser = User::where('social_id', $jwtPayload['sub'])->first();

        if ($findUser)
        {
            $token = auth()->login($findUser);

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message'=> 'Signed in successfully',
                ],
                'data' => [
                    'user' => [
                        'name' => $findUser->name,
                        'email' => $findUser->email,
                        'picture' => $findUser->picture,
                    ],
                    'access_token' => [
                        'token' => $token,
                        'type' => 'Bearer',
                        'expires_in' => strtotime('+' . auth()->factory()->getTTL() . ' minutes'),
                    ],
                ],
            ]);
        }

        $newUser = User::create([
            'name' => $jwtPayload['name'],
            'email' => $jwtPayload['email'],
            'name' => $jwtPayload['name'],
            'picture' => $jwtPayload['picture'],
            'social_id' => $jwtPayload['sub'],
            'social_type' => 'google',
        ]);

        $token = auth()->login($findUser);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message'=> 'Signed in successfully',
            ],
            'data' => [
                'user' => [
                    'name' => $newUser->name,
                    'email' => $newUser->email,
                    'picture' => $newUser->picture,
                ],
                'access_token' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires_in' => strtotime('+' . auth()->factory()->getTTL() . ' minutes'),
                ],
            ],
        ]);
    }
}
