<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 22 Feb 2022 15:05:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace Database\Seeders;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Exception;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect(config("blueprint.permissions"));
        $roles       = collect(config("blueprint.roles"));


        $currentPermissions=Permission::all()->pluck('name');
        $currentPermissions->diff($permissions)
            ->each(function ($permissionName) {
                Permission::where('name', $permissionName)->first()->delete();
            });


        $currentRoles=Role::all()->pluck('name');
        $currentRoles->diff(collect(config("blueprint.roles"))->keys())
            ->each(function ($roleName) {
                Role::where('name', $roleName)->first()->delete();
            });


        $permissions->each(function ($permissionName) {
            try {
                Permission::create(['name' => $permissionName]);
            } catch (Exception) {
            }
        });


        $roles->each(function ($permission_names, $role_name) {
            if (!$role = (new Role())->where('name', $role_name)
                ->first()) {
                $role = Role::create(['name' => $role_name]);
            }
            $permissions = [];
            foreach ($permission_names as $permission_name) {
                if ($permission = (new Permission())->where('name', $permission_name)->first()) {
                    $permissions[] = $permission;
                }
            }

            $role->syncPermissions($permissions);
        });
    }
}
