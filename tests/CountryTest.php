<?php

use Firebase\JWT\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class CountryTest extends TestCase
{
    public function testGetCountries()
    {
        $this->json('GET', '/country')->seeStatusCode(200)->seeJsonStructure([
            'status' => [
                'code', 'message', 'request', 'api-version'
            ],
            'data' => [
                ['idPays', 'nomPays', 'code']
            ], 'count'
        ]);
    }
}
