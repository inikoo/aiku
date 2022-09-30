<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 27 Sept 2022 17:27:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Aurora;

use Illuminate\Validation\ValidationException;
use stdClass;
use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class FetchDeliveryTest extends TestCase
{




    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh','devops/devel/snapshots/empty-aurora-tenants.dump']);
        $process->run();

    }

    public function testAdminUserCommands()
    {

        foreach(json_decode(env('TENANTS')) as $tenantCode){
            $this->artisan("fetch:shippers $tenantCode")->assertExitCode(0);

        }



    }







}
