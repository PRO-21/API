<?php

use Firebase\JWT\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    public function testAuthenticateUsingCredentials()
    {
        $this->json('POST', '/auth', [
            'auth_type' => 'credentials',
            'email' => 'albert.dupontel@bluewin.com',
            'password' => 'pass'
            ])->seeStatusCode(200);
    }

    public function testAuthenticateUsingWrongCredentials()
    {
        $this->json('POST', '/auth', [
            'auth_type' => 'credentials',
            'email' => 'albert.dupontel@office.com',
            'password' => 'passw0rd'
            ])->seeStatusCode(401)->seeJsonEquals(array (
                'status' => 
                array (
                  'code' => 401,
                  'message' => 'Erreur d\'authentification : email ou mot de passe incorrect',
                  'request' => '/auth',
                  'api-version' => env('APP_VERSION'),
                ),
                'data' => NULL,
              ));
    }

    public function testAuthenticateUsingToken()
    {
        $this->json('POST', '/auth', [ 'auth_type' => 'token' ], ['Authorization' => 'Bearer '.env('TEST_TOKEN')])->seeStatusCode(200);
    }

    public function testAuthenticateUsingWrongToken()
    {
        $this->json('POST', '/auth', [ 'auth_type' => 'token' ], ['Authorization' => 'Bearer '.env('TEST_TOKEN').'asd'])->seeStatusCode(401)
        ->seeJsonEquals(array (
            'status' => 
            array (
              'code' => 401,
              'message' => 'Erreur d\'authentification',
              'request' => '/auth',
              'api-version' => env('APP_VERSION'),
            ),
            'data' => NULL,
          ));
    }
}
