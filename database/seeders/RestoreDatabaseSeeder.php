<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 23 Aug 2021 18:02:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;

class RestoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Process::run("psql -d ".env('DB_DATABASE_TEST', 'aiku_test')." -qc 'drop SCHEMA IF EXISTS aiku_awa CASCADE'");
        Process::run("pg_restore -U ".env('DB_USERNAME')." -c -d ".env('DB_DATABASE', 'aiku_test')." ./devops/devel/snapshots/seeded-central-db.dump");

    }
}
