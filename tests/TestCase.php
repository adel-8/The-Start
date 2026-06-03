<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles only if they don't exist
        if (DB::table('roles')->count() === 0) {
            Artisan::call('db:seed', ['--class' => 'RolesTableSeeder']);
        }
        $this->withoutVite();
    }
}
