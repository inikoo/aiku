<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 29 Sept 2022 14:46:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Feature\Sysadmin;


use App\Models\Central\Tenant;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class SeedingPermissionsTest extends TestCase
{


    private int $originalNumberPermissionsInSuperAdmin;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $process = new Process(['devops/load_test_snapshot.sh', 'devops/devel/snapshots/empty-aurora-tenants.dump']);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->originalNumberPermissionsInSuperAdmin = 5;
    }

    public function testPermissionSeeding()
    {
        $permissions = config("blueprint.permissions");

        $originalPermissionsCount = count($permissions);


        $tenant = Tenant::find(1);
        $this->artisan("tenants:seed --tenants=$tenant->id")->assertExitCode(0);

        $tenant->run(function () use ($originalPermissionsCount) {
            $this->assertEquals($originalPermissionsCount, Permission::count());

            $superAdminRole = Role::where('name', 'super-admin')->first();
            $this->assertEquals($this->originalNumberPermissionsInSuperAdmin, $superAdminRole->permissions->count());
        });
    }

    public function testRemovingPermissions()
    {
        $originalPermissionsCount = count(config("blueprint.permissions"));
        $permissions              = config("blueprint.permissions");

        if (($key = array_search('tenant', $permissions)) !== false) {
            unset($permissions[$key]);
        }
        if (($key = array_search('tenant.view', $permissions)) !== false) {
            unset($permissions[$key]);
        }
        config(['blueprint.permissions' => $permissions]);

        $tenant = Tenant::find(1);
        $this->artisan("tenants:seed --tenants=1")->assertExitCode(0);
        $tenant->run(function () use ($originalPermissionsCount) {
            $this->assertEquals(($originalPermissionsCount - 2), Permission::count());

            $superAdminRole = Role::where('name', 'super-admin')->first();
            $this->assertEquals(
                $this->originalNumberPermissionsInSuperAdmin - 1,
                $superAdminRole->permissions->count()
            );
        });
    }

    public function testRemovingRole()
    {
        $roles = config("blueprint.roles");

        $originalRolesCount = count($roles);

        unset($roles['system-admin']);

        config(['blueprint.roles' => $roles]);


        $this->assertCount($originalRolesCount - 1, config("blueprint.roles"));

        $tenant = Tenant::find(1);
        $this->artisan("tenants:seed --tenants=1")->assertExitCode(0);
        $tenant->run(function () use ($originalRolesCount) {
            $this->assertEquals(($originalRolesCount - 1), Role::count());
        });
    }


}
