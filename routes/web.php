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
    echo hash("sha512", "pass");
    return $router->app->version();
});

//Correspond aux URL avec le préfix ./auth
$router->group(['prefix' => '/auth'], function () use ($router) {
    $router->post('/', 'AuthController@authenticate');
});

//Correspond aux URL avec le préfix ./user
$router->group(['prefix' => '/user'], function () use ($router) {
    $lettersRegex = '[A-Za-z]+';
    $digitsRegex = '\d+';
    $emailRegex = "(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*)@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])";
        
    $router->post('/', 'UserController@addUser');

    $router->get("/{id:$digitsRegex}", [
        'middleware' => 'jwt.UserAuth',
        'uses' => 'UserController@getUser'
    ]);

    // id, prenom, nom, adresse, npa, typeCompte, email, [password]
    $router->put("/", [
        'middleware' => 'jwt.UserAuth',
        'uses' => 'UserController@editUser'
    ]);
});

//Correspond aux URL avec le préfix ./cert
$router->group(['prefix' => '/cert'], function () use ($router) {
  $router->post('/', 'CertController@addCert');
  $router->get('/', 'CertController@getCert');
});

