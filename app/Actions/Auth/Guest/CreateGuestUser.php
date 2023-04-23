<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Actions\Auth\User\StoreUser;
use App\Actions\Central\CentralUser\StoreCentralUser;
use App\Actions\WithTenantsArgument;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Central\CentralUser;
use App\Rules\AlphaDashDot;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Spatie\Multitenancy\Landlord;

class CreateGuestUser
{
    use AsAction;
    use WithAttributes;
    use WithTenantsArgument;


    public string $commandSignature = 'create:guest-user {username} {name} {tenants?*} {--E|email=} {--N|name=} {--r|roles=*} {--a|autoPassword}';

    private ?CentralUser $centralUser = null;


    public function getCommandDescription(): string
    {
        return 'Create tenant guest user.';
    }


    /**
     * @param  array  $guestUserData
     * @param  array  $roles  array of Role models
     *
     * @return \App\Models\Auth\User
     */
    public function handle(array $guestUserData, array $roles): User
    {
        $centralUser = Landlord::execute(fn () => StoreCentralUser::run(
            Arr::only($guestUserData, ['username', 'password', 'email', 'name']),
        ));


        $guest = StoreGuest::run(
            array_merge(Arr::only($guestUserData, ['name', 'email']), ['slug' => Arr::get($guestUserData, 'username')])
        );


        /** @var User $user */
        $user = StoreUser::run(app('currentTenant'), $guest, $centralUser);


        foreach ($roles as $role) {
            $user->assignDirectRole($role);
        }


        return $user;
    }


    public function rules(): array
    {
        return [

            'username' => ['required', new AlphaDashDot(), 'unique:App\Models\Central\CentralUser,username'],
            'password' => ['required', app()->isProduction() ? Password::min(8)->uncompromised() : null],
            'name'     => 'sometimes|required',
            'email'    => 'sometimes|required|email'
        ];
    }

    public function asCommand(Command $command): int
    {
        $this->centralUser = null;
        if ($command->option('autoPassword')) {
            $password = (app()->isProduction() ? wordwrap(Str::random(), 4, '-', true) : 'hello');
        } else {
            $password = $command->secret('What is the password?');
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
            $result = (int)$tenant->execute(
                function () use ($validatedData, $command, $tenant) {
                    $roles = [];
                    foreach ($command->option('roles') as $roleName) {
                        /** @var Role $role */
                        if ($role = Role::where('name', $roleName)->first()) {
                            $roles[] = $role;
                        } else {
                            $command->error("Role $roleName not found");
                        }
                    }


                    if ($this->centralUser && $this->centralUser->id) {
                        $guest = CreateGuestFromCentralUser::run(
                            $this->centralUser,
                            array_merge(
                                ['email' => $this->centralUser->email],
                                ['name' => $command->argument('name')],
                            ),
                            $roles
                        );
                        $user  = $guest->user;
                    } else {
                        $user              = $this->handle($validatedData, $roles);
                        $this->centralUser = CentralUser::where('id', $user->central_user_id)->firstOrFail();
                    }


                    /** @var \App\Models\Auth\Guest $guest */
                    $guest = $user->parent;

                    $command->line("Guest user created $user->username");

                    $command->table(
                        ['Tenant', 'Username', 'Global Id', 'Password', 'Email', 'Name', 'Roles'],
                        [
                            [
                                $tenant->code,
                                $user->username,
                                $user->central_user_id,
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
