<?php

use Illuminate\Support\Facades\DB;

class UserTest extends TestCase
{
    public function testGetUser() {
        $this->json('GET', '/user/1', [], ['Authorization' => 'Bearer '.env('TEST_TOKEN')])->seeStatusCode(200)->seeJsonEquals([
            'status' => [
              'code' => 200,
              'message' => 'OK',
              'request' => '/user/1',
              'api-version' => env('APP_VERSION'),
            ],
            'data' => [
              'idPersonne' => 1,
              'prenom' => 'Albert',
              'nom' => 'Dupontel',
              'adresse' => 'Rue de la Côte 56',
              'npa' => '2000',
              'typeCompte' => 'pro',
              'email' => 'albert.dupontel@bluewin.com',
            ],
            'count' => 1,
          ]);
    }

    public function testGetAnotherUser() {
        // Créer un utilisateur temporaire pour tester
        $id = DB::table('Personne')->insertGetId([
            'prenom' => 'Jean',
            'nom' => 'Laserre',
            'adresse' => 'Rue de la Baie 18',
            'npa' => '3005',
            'email' => 'laserrej@swisscom.ch',
            'motDePasse' => 'pass',
          ]);

        $this->json('GET', "/user/$id", [], ['Authorization' => 'Bearer '.env('TEST_TOKEN')])->seeStatusCode(403)->seeJsonEquals([
            'status' => [
              'code' => 403,
              'message' => 'Accès interdit',
              'request' => "/user/$id",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);

          // Supprimer l'utliisateur temporaire
          DB::table('Personne')->where('idPersonne', '=', $id)->delete();
    }

    public function testGetNonExistingUser() {
        $nonExistingId = 9999;
        $this->json('GET', "/user/$nonExistingId", [], ['Authorization' => 'Bearer '.env('TEST_TOKEN')])->seeStatusCode(404)->seeJsonEquals([
            'status' => [
              'code' => 404,
              'message' => 'Aucune information trouvée',
              'request' => "/user/$nonExistingId",
              'api-version' => env('APP_VERSION'),
            ],
            'data' => NULL,
          ]);
    }
}
