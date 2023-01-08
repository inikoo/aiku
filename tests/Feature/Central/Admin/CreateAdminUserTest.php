<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 26 Sept 2022 02:16:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Feature\Central\Admin;

use Illuminate\Validation\ValidationException;
use stdClass;
use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{




    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh','devops/devel/snapshots/initialise-dbs.dump']);
        $process->run();

    }

    public function testAdminUserCommands()
    {

        $adminUserAData=new stdClass();
        $adminUserAData->name=fake()->name();
        $adminUserAData->email=fake()->unique()->safeEmail();
        $adminUserAData->username=fake()->userName();


        $this->artisan("create:admin-user  $adminUserAData->username '$adminUserAData->name' $adminUserAData->email -a")->assertExitCode(0);

        $this->artisan("create:admin-token non_existent_username admin root")->assertExitCode(1);
        $this->artisan("create:admin-token $adminUserAData->username admin root")->assertExitCode(0);


        $this->expectException(ValidationException::class);
        $this->artisan("create:admin-user  $adminUserAData->username '$adminUserAData->name' $adminUserAData->email -a")->assertExitCode(1);



    }







}
