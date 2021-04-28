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
        return require __DIR__.'/../bootstrap/app.php';
    }
}
