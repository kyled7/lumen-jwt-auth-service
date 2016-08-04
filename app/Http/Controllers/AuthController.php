<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    protected $githubProvider;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        $this->githubProvider = Socialite::driver('github');
        $this->githubProvider->stateless();
        $this->githubProvider->scopes([
            'user:email',
            'repo',
            'write:repo_hook',
        ]);
    }

    public function postSignin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        try {
            if (!$token = $this->jwt->attempt($request->only('email', 'password'))) {
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
            'username' => 'required',
        ]);

        try {
            $user = $this->create($request->only('email', 'username', 'password'));
        } catch (\Exception $e) {
            return response()->json('cannot_save_user', 500);
        }

        $token = $this->jwt->fromUser($user);

        return response()->json(compact('token'), 201);
    }

    public function postGithubCallback(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $githubUser = $this->githubProvider->user();

        //Find user by github id
        $user = User::where('github', $githubUser->getId())->first();
        if (!$user) {

            //Find user by email
            $user = User::where('email', $githubUser->getEmail())->first();
            if ($user) {
                $user->github = $githubUser->getId();
                $user->save();
            } else {
                try {
                    $user = User::create([
                        'username' => $githubUser->getNickname(),
                        'email'    => $githubUser->getEmail(),
                        'avatar'   => $githubUser->getAvatar(),
                        'github'   => $githubUser->getId(),
                    ]);
                } catch (\Exception $e) {
                    return response()->json('cannot_save_user', 500);
                }
            }
        }

        return response()->json([
            'token' => $this->jwt->fromUser($user),
        ]);
    }

    public function getGithubAuthUrl()
    {
        return response()->json($this->githubProvider->redirect()->getTargetUrl(), 200);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => app('hash')->make($data['password']),
        ]);
    }
}
