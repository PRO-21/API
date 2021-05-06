<?php

use Illuminate\Support\Facades\DB;

class UserTest extends TestCase
{
    protected $userId;
    protected $otherUserId;
    protected $userToken;

    protected function setUp() : void {
        parent::setUp();

        // Créer l'utilisateur de test
        $this->userId = DB::table('Personne')->insertGetId([
            'prenom' => 'Albert',
            'nom' => 'Dupontel',
            'adresse' => 'Rue de la Côte 56',
            'npa' => '2000',
            'typeCompte' => 'pro',
            'email' => 'albert.dupontel@bluewin.com',
            'motDePasse' => '5b722b307fce6c944905d132691d5e4a2214b7fe92b738920eb3fce3a90420a19511c3010a0e7712b054daef5b57bad59ecbd93b3280f210578f547f4aed4d25', // pass
            'idPersonnePays' => 'CHE'
        ]);

        $this->otherUserId = DB::table('Personne')->insertGetId([
            'prenom' => 'Jean',
            'nom' => 'Laserre',
            'adresse' => 'Rue de la Baie 18',
            'npa' => '3005',
            'email' => 'laserrej@swisscom.ch',
            'motDePasse' => '5b722b307fce6c944905d132691d5e4a2214b7fe92b738920eb3fce3a90420a19511c3010a0e7712b054daef5b57bad59ecbd93b3280f210578f547f4aed4d25', // pass
            'idPersonnePays' => 'CHE'
          ]);

        // Récupérer un token pour l'utilisateur
        $json = $this->post('/auth', [
            'auth_type' => 'credentials',
            'email' => 'albert.dupontel@bluewin.com',
            'password' => 'pass'
        ])->response->getContent();
            
        $this->userToken = json_decode($json)->data->token;
    }

    protected function tearDown() : void {
        // Suppr l'utilisateur de test
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('Personne')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    public function testGetUser() {
        $this->json('GET', "/user/$this->userId", [], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(200)->seeJsonEquals([
            'status' => [
              'code' => 200,
              'message' => 'OK',
              'request' => "/user/$this->userId",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => [
              'idPersonne' => $this->userId,
              'prenom' => 'Albert',
              'nom' => 'Dupontel',
              'adresse' => 'Rue de la Côte 56',
              'npa' => '2000',
              'typeCompte' => 'pro',
              'email' => 'albert.dupontel@bluewin.com',
              "idPays" => "CHE",
              "nomPays" => "Switzerland",
              "codePays" => "CH"
            ],
            'count' => 1,
          ]);
    }

    public function testGetAnotherUser() {

        $this->json('GET', "/user/$this->otherUserId", [], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(403)->seeJsonEquals([
            'status' => [
              'code' => 403,
              'message' => 'Accès interdit',
              'request' => "/user/$this->otherUserId",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testGetNonExistingUser() {
        $nonExistingId = 9999;
        $this->json('GET', "/user/$nonExistingId", [], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(404)->seeJsonEquals([
            'status' => [
              'code' => 404,
              'message' => 'Aucune information trouvée',
              'request' => "/user/$nonExistingId",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testCreateUser() {
        $this->json('POST', "/user", [
            'prenom' => 'Pierre',
            'nom' => 'Poruy',
            'adresse' => 'Chemin du Cassis 12',
            'npa' => '1001',
            'email' => 'poruy.pierre@gmail.com',
            'motDePasse' => 'pass',
            "idPersonnePays" => "CHE"
          ])->seeStatusCode(200)->seeJson([ // Ne peut pas tester le retour exact car ne peut pas prédir l'id du nouvel utilisateur
            'status' => [
              'code' => 200,
              'message' => 'OK',
              'request' => '/user',
              'api-version' => env('APP_VERSION'),
            ]
          ]);
    }

    public function testCreateUserUsingMissingParameter() {
        $this->json('POST', "/user", [
            'prenom' => 'Pierre',
            'nom' => 'Poruy',
            'adresse' => 'Chemin du Cassis 12',
            'npa' => '1001',
            'motDePasse' => 'pass',
          ])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : il manque un ou des paramètre(s)',
              'request' => '/user',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testCreateUserUsingInvalidEmail() {
        $this->json('POST', "/user", [
            'prenom' => 'Pierre',
            'nom' => 'Poruy',
            'adresse' => 'Chemin du Cassis 12',
            'npa' => '1001',
            'email' => 'poruy.pierre@gmailcom',
            'motDePasse' => 'pass',
            "idPersonnePays" => "CHE"
          ])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : email incorrect',
              'request' => '/user',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testCreateUserUsingAlreadyUsedEmail() {
        $this->json('POST', "/user", [
            'prenom' => 'Pierre',
            'nom' => 'Poruy',
            'adresse' => 'Chemin du Cassis 12',
            'npa' => '1001',
            'email' => 'albert.dupontel@bluewin.com',
            'motDePasse' => 'pass',
            "idPersonnePays" => "CHE"
          ])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : cet email est déjà utilisé',
              'request' => '/user',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testEditUser() {
        $this->json('PATCH', "/user", [
            'idPersonne' => $this->userId,
            'adresse' => "Rue de la Côte 18"
        ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(200)->seeJsonEquals([
            'status' => [
              'code' => 200,
              'message' => 'OK',
              'request' => "/user",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => [
                [
                    'idPersonne' => $this->userId,
                    'prenom' => 'Albert',
                    'nom' => 'Dupontel',
                    'adresse' => 'Rue de la Côte 18',
                    'npa' => '2000',
                    'typeCompte' => 'pro',
                    'email' => 'albert.dupontel@bluewin.com',
                    "idPays" => "CHE",
                    "nomPays" => "Switzerland",
                    "codePays" => "CH"
                ]
            ],
            'count' => 1,
          ]);
    }

    public function testEditUserUsingNoParameters() {
        $this->json('PATCH', "/user", [ 'idPersonne' => $this->userId ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide',
              'request' => '/user',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);;
    }

    public function testEditUserAnotherUser() {
        $this->json('PATCH', "/user", [
            'idPersonne' => $this->otherUserId,
            'adresse' => "Rue de la Côte 18"
        ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(403)->seeJsonEquals([
            'status' => [
              'code' => 403,
              'message' => 'Accès interdit',
              'request' => "/user",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testEditUnknownUser() {
        $this->json('PATCH', "/user", [
            'idPersonne' => 9999,
            'adresse' => "Rue de la Côte 18"
        ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(404)->seeJsonEquals([
            'status' => [
              'code' => 404,
              'message' => 'Aucune information trouvée',
              'request' => "/user",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

}
