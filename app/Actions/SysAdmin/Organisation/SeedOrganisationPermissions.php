<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 12:29:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Enums\SysAdmin\Authorisation\OrganisationPermissionsEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\PermissionRegistrar;

class SeedOrganisationPermissions
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();


        $organisationPermissions = collect(OrganisationPermissionsEnum::getAllValues());
        $organisationRoles       = collect(RolesEnum::getRolesWithScope('group'));


        $currentPermissions = Permission::where('scope_type', 'organisation')->pluck('name');
        $currentPermissions->diff($organisationPermissions)
            ->each(function ($permissionName) use ($organisation) {
                Permission::where('name', $this->getPermissionName($permissionName, $organisation))->first()->delete();
            });


        $currentRoles = Role::where('scope_type', 'organisation')->pluck('name');
        $currentRoles->diff($organisationRoles)
            ->each(function ($roleName) use ($organisation) {
                Role::where(
                    'name',
                    $this->getRoleName($roleName, $organisation)
                )->first()->delete();
            });


        $organisationPermissions->each(function ($permissionName) use ($organisation) {
            try {
                Permission::create(
                    [
                        'name'       => $this->getPermissionName($permissionName, $organisation),
                        'scope_type' => 'organization',
                        'scope_id'   => $organisation->id,
                    ]
                );
            } catch (Exception) {
            }
        });


        foreach (RolesEnum::cases() as $case) {
            if ($case->scope() === 'organisation') {
                if (!$role = (new Role())->where('name', $this->getRoleName($case->value, $organisation))->first()) {
                    $role = Role::create(
                        [
                            'name'       => $this->getRoleName($case->value, $organisation),
                            'scope_type' => 'organization',
                            'scope_id'   => $organisation->id,
                        ]
                    );
                }
                $organisationPermissions = [];
                foreach ($case->getPermissions() as $permissionName) {
                    if ($permission = (new Permission())->where('name', $this->getPermissionName($permissionName->value, $organisation))->first()) {
                        $organisationPermissions[] =  $permission;
                    }
                }
                $role->syncPermissions($organisationPermissions);
            }
        }
    }


    public function getPermissionName(string $rawName, Organisation $organisation): string
    {
        $permissionComponents = explode('.', $rawName);
        $permissionComponents = array_merge(array_slice($permissionComponents, 0, 1), [$organisation->slug], array_slice($permissionComponents, 1));

        return join('.', $permissionComponents);
    }

    public function getRoleName(string $rawName, Organisation $organisation): string
    {
        return $rawName.'-'.$organisation->slug;
    }

    public string $commandSignature = 'org:seed-permissions';

    public function asCommand(Command $command): int
    {
        foreach (Organisation::all() as $organisation) {
            $command->info("Seeding permissions for org: $organisation->name");
            setPermissionsTeamId($organisation->group_id);
            $this->handle($organisation);
        }

        return 0;
    }

}
