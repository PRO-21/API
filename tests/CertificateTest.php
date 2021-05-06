<?php

use Firebase\JWT\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class CertificateTest extends TestCase
{
    protected $id;
    protected $userToken;

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
        DB::table('Certificat')->truncate();
        DB::table('Champ')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    public function testCreateCertificate()
    {
        $this->json('POST', '/cert', [
            'dateSignature' => '2021-04-07',
            'date du test' => '05.05.2021',
            'résultat du test' => 'NÉGATIF',
            'Lieu du test' => 'Hôpital Pourtales',
          ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(200)->seeJsonStructure([
            'status' => [
                'code', 'message', 'request', 'api-version'
            ],
            'data' => [
                'idCertificat', 'dateSignature',
                'signataire' => ['prenom', 'nom'],
                'champs' => [
                    ['nom', 'valeur'],
                    ['nom', 'valeur'],
                    ['nom', 'valeur']
                ]
            ], 'count'
        ]);
    }

    public function testCreateCertificateUsingMissingParameters()
    {
        $this->json('POST', '/cert', [
            'date du test' => '05.05.2021',
            'résultat du test' => 'NÉGATIF',
            'Lieu du test' => 'Hôpital Pourtales',
          ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : il manque un ou des paramètre(s)',
              'request' => '/cert',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testCreateCertificateUsingWronglyFormattedDate()
    {
        $this->json('POST', '/cert', [
            'dateSignature' => '07.04.2021',
            'date du test' => '05.05.2021',
            'résultat du test' => 'NÉGATIF',
            'Lieu du test' => 'Hôpital Pourtales',
          ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : la date doit être au format yyyy-mm-dd',
              'request' => '/cert',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testCreateCertificateWithoutSpecifyingFields()
    {
        $this->json('POST', '/cert', [
            'dateSignature' => '2021-04-07',
          ], ['Authorization' => 'Bearer '.$this->userToken])->seeStatusCode(400)->seeJsonEquals([
            'status' => [
              'code' => 400,
              'message' => 'Requête invalide : vous n\'avez pas renseigné de champs',
              'request' => '/cert',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }

    public function testGetCertificate() {
        // Créer un certificat
        $json = $this->post('/cert', [
            'dateSignature' => '2021-04-07',
            'date du test' => '05.05.2021',
            'résultat du test' => 'NÉGATIF',
            'Lieu du test' => 'Hôpital Pourtales',
        ], ['Authorization' => 'Bearer '.$this->userToken])->response->getContent();
            
        $certId = json_decode($json)->data->idCertificat;
        $this->json('GET', "/cert/$certId")->seeStatusCode(200)->seeJsonStructure([
            'status' => [
                'code', 'message', 'request', 'api-version'
            ],
            'data' => [
                'idCertificat', 'dateSignature',
                'signataire' => ['prenom', 'nom'],
                'champs' => [
                    ['nom', 'valeur'],
                    ['nom', 'valeur'],
                    ['nom', 'valeur']
                ]
            ], 'count'
        ]);
    }

    public function testGetNonExistingCertificate() {
        $nonExistingId = 9999;
        $this->json('GET', "/cert/$nonExistingId")->seeStatusCode(404)->seeJsonEquals([
            'status' => [
              'code' => 404,
              'message' => 'Aucune information trouvée',
              'request' => "/cert/$nonExistingId",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }


}
