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

    public function testAuthenticateUsingToken()
    {
        $this->json('POST', '/auth', [ 'auth_type' => 'token' ], ['Authorization' => 'Bearer '.env('TEST_TOKEN')])->seeStatusCode(200);
    }
}
