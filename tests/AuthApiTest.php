<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthApiTest extends TestCase
{
    use DatabaseMigrations;

    public function testSigninApiSuccess()
    {
        $user = factory('App\User')->create([
            'password' => app('hash')->make('123456'),
        ]);
        $this->post('/auth/signin', ['email' => $user->email, 'password' => '123456'])
            ->seeJsonStructure(['token']);
    }

    public function testSigninApiFails()
    {
        $this->post('/auth/signin', [])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'password']);

        $this->post('auth/signin', ['email' => 'wrong email format'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'password']);

        $this->post('auth/signin', ['email' => 'email@test.com'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['password']);

        $this->post('auth/signin', ['password' => 'some string'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email']);

        $this->post('auth/signin', ['email' => 'email@test.com', 'password' => 'short'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['password']);

        $this->post('auth/signin', ['email' => 'email@test.com', 'password' => 'password'])
            ->seeStatusCode(404)
            ->seeJson(['user_not_found']);
    }

    public function testSignupApiFails()
    {
        $this->post('/auth/signup', [])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'password', 'username']);

        $this->post('auth/signup', ['email' => 'wrong email format'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'password', 'username']);

        $this->post('auth/signup', ['email' => 'email@test.com'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['password', 'username']);

        $this->post('auth/signup', ['password' => 'some string'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'username']);

        $this->post('auth/signup', ['username' => 'some string'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email', 'password']);

        $this->post('auth/signup', ['username' => 'some string', 'email' => 'mail@test.com', 'password' => 'short'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['password']);

        $user = factory('App\User')->create();
        $this->post('auth/signup', ['username' => 'some string', 'email' => $user->email, 'password' => 'password'])
            ->seeStatusCode(422)
            ->seeJsonStructure(['email']);
    }

    public function testSignupSuccess()
    {
        $this->post('auth/signup', [
            'username' => 'username',
            'email'    => 'email@test.com',
            'password' => 'password',
        ])->seeStatusCode(201)
            ->seeJsonStructure(['token']);

        $this->seeInDatabase('users', ['email' => 'email@test.com']);
    }
}
