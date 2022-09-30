<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 28 Sept 2022 20:21:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Feature\Central\Tenant;


use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class HydrateTenantTest extends TestCase
{




    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh','devops/devel/snapshots/empty-aurora-tenants.dump']);
        $process->run();

    }

    public function testAdminUserCommands()
    {



        $this->artisan("hydrate:tenant")->assertExitCode(0);




    }







}
