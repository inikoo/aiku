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

class CreateGuestFromCentralUser
{
    use AsAction;
    use WithAttributes;
    use WithTenantsArgument;


    public string $commandSignature = 'create:guest-existing-user {username} {name} {tenants?*}  {--r|roles=*}';

    public function getCommandDescription(): string
    {
        return 'Create tenant guest from existing user.';
    }


    /**
     * @param  array  $roles  array of Role models
     *
     */
    public function handle(CentralUser $centralUser, array $modelData, array $roles): Guest
    {
        $modelData['slug'] = Arr::get($modelData, 'slug', $centralUser->username);


        $guest = StoreGuest::run($modelData);
        /** @var User $user */
        $user = StoreUser::run(app('currentTenant'), $guest, $centralUser);
        foreach ($roles as $role) {
            $user->assignDirectRole($role);
        }

        return $guest;
    }


    public function asCommand(Command $command): int
    {
        $centralUser = CentralUser::where('username', $command->argument('username'))->firstOrFail();


        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(
                function () use ($centralUser, $command) {
                    $roles = [];
                    foreach ($command->option('roles') as $roleName) {
                        /** @var Role $role */
                        if ($role = Role::where('name', $roleName)->first()) {
                            $roles[] = $role;
                        } else {
                            $command->error("Role $roleName not found");
                        }
                    }

                    $modelData = array_merge(
                        ['email' => $centralUser->email],
                        ['name' => $command->argument('name')],
                    );
                    $guest     = $this->handle($centralUser, $modelData, $roles);
                    $user      = $guest->user;

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
