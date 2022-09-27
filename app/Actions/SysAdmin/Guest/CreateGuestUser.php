<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 19:58:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;


use App\Actions\Central\CentralUser\StoreCentralUser;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\WithTenantsArgument;

use App\Models\Central\CentralUser;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateGuestUser
{

    use AsAction;
    use WithAttributes;
    use WithTenantsArgument;


    public string $commandSignature = 'create:guest-user {username} {name} {tenants?*} {--E|email=} {--N|name=} {--r|roles=*} {--a|autoPassword}';
    /**
     * @var \App\Models\Central\CentralUser|array|\ArrayAccess|\Illuminate\Database\Eloquent\Model|mixed|null
     */
    private ?CentralUser $centralUser = null;

    public function getCommandDescription(): string
    {
        return 'Create tenant guest user.';
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle($guestUserData, $roles): User
    {
        $guest = StoreGuest::run(Arr::only($guestUserData, ['name', 'email']));


        $centralUser = StoreCentralUser::run(
            array_merge(
                Arr::only($guestUserData, ['username', 'password']),
                ['data' => Arr::only($guestUserData, ['name', 'email'])]
            )
        );
        $user        = StoreUser::run(tenant(), $guest, $centralUser);


        foreach ($roles as $roleName) {
            $user->assignRole($roleName);
        }


        return $user;
    }


    public function rules(): array
    {
        return [

            'username' => ['required', new AlphaDashDot, 'unique:App\Models\Central\CentralUser,username'],
            'password' => ['required', app()->isLocal() ? null : Password::min(8)->uncompromised()],
            'name'     => 'sometimes|required',
            'email'    => 'sometimes|required|email'
        ];
    }

    public function asCommand(Command $command): int
    {
        if ($command->option('autoPassword')) {
            $password = (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true));
        } else {
            $password = $this->$command('What is the password?');
        }


        $this->fill([
                        'username' => $command->argument('username'),
                        'password' => $password,
                        'name'     => $command->argument('name'),
                    ]);
        if ($command->option('email')) {
            $this->fill([
                            'email' => $command->option('email')
                        ]);
        }

        $validatedData = $this->validateAttributes();


        $tenants  = $this->getTenants($command);
        $exitCode = 0;


        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(
            /**
             * @throws \Illuminate\Validation\ValidationException
             */ function () use ($validatedData, $command, $tenant) {
                $roles = [];
                foreach ($command->option('roles') as $roleName) {
                    /** @var Role $role */
                    if ($role = Role::where('name', $roleName)->first()) {
                        $roles[] = $role->name;
                    } else {
                        $command->error("Role $roleName not found");
                    }
                }


                if (!$this->centralUser) {
                    $user              = $this->handle($validatedData, $roles);
                    $this->centralUser = CentralUser::where('global_id', $user->getGlobalIdentifierKey())->firstOrFail();
                } else {
                    $user = CreateGuestFromExistingUser::run($this->centralUser, $roles);
                }


                /** @var Guest $guest */
                $guest = $user->parent;

                $command->line("Guest user created $user->username");

                $command->table(
                    ['Tenant', 'Username', 'Global Id', 'Password', 'Email', 'Name', 'Roles'],
                    [
                        [
                            $tenant->code,
                            $user->username,
                            $user->getGlobalIdentifierKey(),
                            ($command->option('autoPassword') ? Arr::get($validatedData, 'password') : '*****'),
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
