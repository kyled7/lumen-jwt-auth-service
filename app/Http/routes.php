<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post('/auth/signin', 'AuthController@postSignin');
$app->post('/auth/signup', 'AuthController@postSignup');
$app->post('/auth/github', 'AuthController@postGithubCallback');
$app->get('/auth/githubAuthUrl', 'AuthController@getGithubAuthUrl');
