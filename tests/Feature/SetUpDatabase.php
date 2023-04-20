<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 10:14:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SetUpDatabase extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_set_up_database(): void
    {
//        Artisan::call('migrate:reset --path=database/migrations/central  --database=central');
//        exec('pg_restore -U aiku -c -d aiku_test ./devops/devel/snapshots/seeded-central-db.dump');

        $this->assertTrue(true);
    }
}
