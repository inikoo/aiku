<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 27 Sept 2022 13:51:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Aurora;

use Illuminate\Validation\ValidationException;
use stdClass;
use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class FetchShopsTest extends TestCase
{




    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh','devops/devel/snapshots/migrate-aurora-tenants.dump']);
        $process->run();

    }

    public function testAdminUserCommands()
    {

        foreach(json_decode(env('TENANTS_DATA')) as $tenantCode=>$foo){
            $this->artisan("fetch:shops $tenantCode")->assertExitCode(0);

        }



    }







}
