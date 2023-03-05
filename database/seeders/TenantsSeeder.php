<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 05:14:25 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantsSeeder extends Seeder
{
    public function run()
    {
        $this->call([
                        PermissionSeeder::class,
                        JobPositionSeeder::class,
                    ]);
    }
}
