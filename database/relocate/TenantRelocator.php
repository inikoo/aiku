<?php
/*
Author: Raul Perusquía (raul@inikoo.com)
Created:  Mon Jul 27 2020 17:38:26 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Tenant;

class TenantRelocator extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {
        $database_names = preg_split('/,/', env('MIGRATION_DATABASE_NAMES'));
        $tenant_codes   = preg_split('/,/', env('MIGRATION_TENANT_CODES'));
        $legacy_codes   = preg_split('/,/', env('MIGRATION_ACCOUNT_LEGACY_CODES'));

        foreach ($tenant_codes as $index => $tenant_code) {
            Tenant::firstOrCreate(
                [
                    'name' => $tenant_code,
                ], [
                    'subdomain' => Str::kebab($tenant_code),
                    'database' => 'au_'. Str::kebab($tenant_code),

                    'data'     => [
                        'legacy_code' => $legacy_codes[$index],
                        'legacy_database' => $database_names[$index],

                    ],
                    'settings' => [
                        'recover_email' => 'recover@inikoo.com',
                        'recover_pin'   => hash('crc32', rand(0, 10000))
                    ]
                ]
            );
        }


    }
}
