<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    public $testUserId;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        // S'assurer que la table Personne est vide
        DB::table('Personne')->truncate();

        // Créer l'utilisateur de test
        $this->testUserId = DB::table('Personne')->insertGetId([
            'prenom' => 'Albert',
            'nom' => 'Dupontel',
            'adresse' => 'Rue de la Côte 56',
            'npa' => '2000',
            'typeCompte' => 'pro',
            'email' => 'albert.dupontel@bluewin.com',
            'motDePasse' => '5b722b307fce6c944905d132691d5e4a2214b7fe92b738920eb3fce3a90420a19511c3010a0e7712b054daef5b57bad59ecbd93b3280f210578f547f4aed4d25' // pass
            ]);

        return require __DIR__.'/../bootstrap/app.php';
    }
}
