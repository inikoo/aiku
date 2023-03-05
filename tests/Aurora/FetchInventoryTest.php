<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 27 Sept 2022 14:39:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Aurora;

use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class FetchInventoryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh','devops/devel/snapshots/empty-aurora-tenants.dump']);
        $process->run();
    }

    public function testAdminUserCommands()
    {
        foreach (json_decode(env('TENANTS')) as $tenantCode) {
            $this->artisan("fetch:warehouses $tenantCode")->assertExitCode(0);
            $this->artisan("fetch:warehouse-areas $tenantCode")->assertExitCode(0);
            $this->artisan("fetch:locations $tenantCode")->assertExitCode(0);
            $this->artisan("fetch:stocks $tenantCode")->assertExitCode(0);
        }
    }
}
