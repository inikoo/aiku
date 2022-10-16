<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 29 Sept 2022 14:46:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace Tests\Feature\Sysadmin;


use App\Actions\HumanResources\AttachJobPosition;
use App\Actions\HumanResources\DetachJobPosition;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as Process;
use Tests\TestCase;

class AuthorisationTest extends TestCase
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
        $this->originalNumberPermissionsInSuperAdmin = 6;
        $this->tenant = Tenant::find(1);

    }

    public function testPermissionSeeding()
    {
        $permissions = config("blueprint.permissions");

        $originalPermissionsCount = count($permissions);


        $this->artisan("tenants:seed --tenants={$this->tenant->id}")->assertExitCode(0);

        $this->tenant->run(function () use ($originalPermissionsCount) {
            $this->assertEquals($originalPermissionsCount, Permission::count());

            $superAdminRole = Role::where('name', 'super-admin')->first();
            $this->assertEquals($this->originalNumberPermissionsInSuperAdmin, $superAdminRole->permissions->count());
        });
    }


    public function testEmployeeAuthorisation()
   {
       $this->tenant->run(function ()  {
           /** @var Employee $employee */
           $employee = Employee::factory()->create();
           $jobPositionHR=JobPosition::firstWhere('slug','hr-m');
           AttachJobPosition::run($employee,$jobPositionHR);

           $this->assertTrue($employee->jobPositions->contains($jobPositionHR));

           $username=fake()->userName();
           $user=CreateUserFromEmployee::run(employee:$employee,username:$username);
           $employee->refresh();

           $this->assertInstanceOf(User::class, $user);
           $this->assertEquals($username,$user->username);

           $this->assertInstanceOf(User::class, $employee->user);


           $this->assertCount(1,$user->roles);
           $this->assertTrue($user->hasRole('human-resources-admin'));

           $jobPositionWarehouse=JobPosition::firstWhere('slug','wah-m');
           AttachJobPosition::run($employee,$jobPositionWarehouse);
           $user->refresh();

           $this->assertCount(2,$user->roles);
           $this->assertTrue($user->hasRole('distribution-admin'));

           DetachJobPosition::run($employee,$jobPositionWarehouse);
           $user->refresh();
           $this->assertCount(1,$user->roles);



           $role=Role::firstWhere('name','super-admin');
           $user->assignDirectRole($role);
           $this->assertCount(2,$user->roles);
           $this->assertTrue($user->hasRole('super-admin'));

           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->direct_role);
           $this->assertFalse($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->job_position_role);

           $jobPositionDirector=JobPosition::firstWhere('slug','dir');
           AttachJobPosition::run($employee,$jobPositionDirector);
           $user->refresh();
           $this->assertCount(2,$user->roles);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->direct_role);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->job_position_role);

           DetachJobPosition::run($employee,$jobPositionDirector);
           $user->refresh();
           $this->assertCount(2,$user->roles);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->direct_role);
           $this->assertFalse($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->job_position_role);

           $role=Role::firstWhere('name','human-resources-admin');
           $user->assignDirectRole($role);
           $this->assertCount(2,$user->roles);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->direct_role);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->job_position_role);

           $user->removeDirectRole($role);
           $this->assertCount(2,$user->roles);
           $this->assertFalse($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->direct_role);
           $this->assertTrue($user->roles()->wherePivot('role_id',$role->id)->first()->pivot->job_position_role);


           DetachJobPosition::run($employee,$jobPositionHR);
           $user->refresh();
           $this->assertCount(1,$user->roles);

           $role=Role::firstWhere('name','super-admin');
           $user->removeDirectRole($role);
           $this->assertCount(0,$user->roles);

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


        $this->artisan("tenants:seed --tenants={$this->tenant->id}")->assertExitCode(0);
        $this->tenant->run(function () use ($originalPermissionsCount) {
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
        $roles              = config("blueprint.roles");
        $originalRolesCount = count($roles);
        unset($roles['system-admin']);
        config(['blueprint.roles' => $roles]);


        $this->assertCount($originalRolesCount - 1, config("blueprint.roles"));


        $this->artisan("tenants:seed --tenants={$this->tenant->id}")->assertExitCode(0);
        $this->tenant->run(function () use ($originalRolesCount) {
            $this->assertEquals(($originalRolesCount - 1), Role::count());
        });
    }


}
