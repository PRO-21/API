<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//Correspond aux URL avec le préfix ./auth
$router->group(['prefix' => '/auth'], function () use ($router) {
  $router->post('/', 'AuthController@authenticate');
});

//Correspond aux URL avec le préfix ./user
$router->group(['prefix' => '/user'], function () use ($router) {
  $router->post('/', 'UserController@addUser');
  $router->get('/', 'UserController@getUser');
  $router->put('/', 'UserController@editUser');
});

//Correspond aux URL avec le préfix ./cert
$router->group(['prefix' => '/cert'], function () use ($router) {
  $router->post('/', 'CertController@addCert');
  $router->get('/', 'CertController@getCert');
});
