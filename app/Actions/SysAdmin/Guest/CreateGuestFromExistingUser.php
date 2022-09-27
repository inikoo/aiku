<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 27 Sept 2022 10:23:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;


use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\WithTenantsArgument;

use App\Models\Central\CentralUser;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateGuestFromExistingUser
{

    use AsAction;
    use WithAttributes;
    use WithTenantsArgument;


    public string $commandSignature = 'create:guest-existing-user {global_id}  {tenants?*}  {--r|roles=*}';

    public function getCommandDescription(): string
    {
        return 'Create tenant guest from existing user.';
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(CentralUser $centralUser, $roles): User
    {
        $guest = StoreGuest::run(
            Arr::only($centralUser->data, ['name', 'email'])
        );
        $user  = StoreUser::run(tenant(), $guest, $centralUser);
        foreach ($roles as $roleName) {
            $user->assignRole($roleName);
        }

        return $user;
    }


    public function asCommand(Command $command): int
    {
        $centralUser = CentralUser::where('global_id', $command->argument('global_id'))->firstOrFail();


        $tenants = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(
            /**
             * @throws \Illuminate\Validation\ValidationException
             */ function () use ($centralUser, $command) {
                $roles = [];
                foreach ($command->option('roles') as $roleName) {
                    /** @var Role $role */
                    if ($role = Role::where('name', $roleName)->first()) {
                        $roles[] = $role->name;
                    } else {
                        $command->error("Role $roleName not found");
                    }
                }


                $user = $this->handle($centralUser, $roles);
                /** @var Guest $guest */
                $guest = $user->parent;

                $command->line("Guest user created $user->username");

                $command->table(
                    ['Username', 'Email', 'Name', 'Roles'],
                    [
                        [
                            $user->username,
                            $guest->email,
                            $guest->name,
                            $user->getRoleNames()->implode(',')
                        ],

                    ]
                );
            }
            );

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


}
