<?php

namespace Database\Seeders;


use App\Models\System\Admin;
use Illuminate\Database\Seeder;

use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NewTenantSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {

        app()[PermissionRegistrar::class]->forgetCachedPermissions();


            Permission::create(['name' => 'users.create']);
            Permission::create(['name' => 'users.edit']);
            Permission::create(['name' => 'users.delete']);
            Permission::create(['name' => 'users.*']);
            Permission::create(['name' => 'look-and-field']);


            Permission::create(['name' => 'employees.edit']);
            Permission::create(['name' => 'employees.delete']);
            Permission::create(['name' => 'employees.payroll']);
            Permission::create(['name' => 'employees.confidential']);
            Permission::create(['name' => 'employees.attendance']);

            Permission::create(['name' => 'employees.*']);


            Role::create(['name' => 'super-system-admin'])->givePermissionTo(Permission::all());

            Role::create(['name' => 'system-admin'])->givePermissionTo(
                [
                    'users.*',
                    'look-and-field',
                ]
            );
            Role::create(['name' => 'human-resources-clerk'])->givePermissionTo(
                [
                    'employees.edit',
                    'employees.attendance',
                ]
            );
            Role::create(['name' => 'human-resources-admin'])->givePermissionTo(
                [
                    'employees.*'
                ]
            );

            $tenant = app('currentTenant');

            $superAdmin = Admin::create([
                                            'slug' => 'super-admin',
                                            'name' => 'Super Admin',
                                            'tenant_id'=>$tenant->id
                                        ]);

            $superAdmin->user->assignRole('super-system-admin');




    }
}
