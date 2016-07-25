<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postSignin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        try {

            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
    }

    public function postSignup(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'username' => 'required'
        ]);

        try {
            $user = User::create($request->only('email', 'username', 'password'));
        } catch (\Exception $e) {
            return response()->json('cannot_save_user', 500);
        }

        $token = $this->jwt->fromUser($user);
        return response()->json(compact('token'), 201);
    }
}