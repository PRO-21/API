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

    $router->get("/{id:\d+}", [
        'middleware' => 'jwt.UserAuth',
        'uses' => 'UserController@getUser'
    ]);

    // id, prenom, nom, adresse, npa, typeCompte, email, [password]
    $router->patch("/", [
        'middleware' => 'jwt.UserAuth',
        'uses' => 'UserController@editUser'
    ]);
});

//Correspond aux URL avec le préfix ./cert
$router->group(['prefix' => '/cert'], function () use ($router) {
    $router->post("/", [
        'middleware' => 'jwt.UserAuth',
        'uses' => 'CertController@addCert'
    ]);

    $router->get('/{id}', 'CertController@getCert');
});

