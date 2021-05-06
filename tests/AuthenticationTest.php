<?php

use Firebase\JWT\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class AuthenticationTest extends TestCase
{
    protected $id;

    protected function setUp() : void {
        parent::setUp();

        // Créer l'utilisateur de test
        $this->id = DB::table('Personne')->insertGetId([
            'prenom' => 'Albert',
            'nom' => 'Dupontel',
            'adresse' => 'Rue de la Côte 56',
            'npa' => '2000',
            'typeCompte' => 'pro',
            'email' => 'albert.dupontel@bluewin.com',
            'motDePasse' => '5b722b307fce6c944905d132691d5e4a2214b7fe92b738920eb3fce3a90420a19511c3010a0e7712b054daef5b57bad59ecbd93b3280f210578f547f4aed4d25', // pass
            'idPersonnePays' => 'CHE'
        ]);
    }

    protected function tearDown() : void {
        // Suppr l'utilisateur de test
        DB::table('Personne')->where('idPersonne', '=', $this->id)->delete();
        parent::tearDown();
    }

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
