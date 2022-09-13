<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 15:01:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use App\Actions\StoreModelAction;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;


class CreateGuestUser extends StoreModelAction
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'new:guest-user {organisation_code} {username} {name} {--E|email=} {--N|name=} {--r|role=*} {--a|autoPassword}';


    public function handle(Organisation $organisation, array $modelData): ActionResult
    {
        $res = StoreGuest::run(
            $organisation,
            [
                'code' => $modelData['username'],
                'name' => $modelData['name']
            ]
        );

        $guest = $res->model;
        $res   = StoreUser::run($modelData);
        $user  = $res->model;

        return AttachUserToOrganisation::run($guest, $user);
    }


    public function rules(): array
    {
        return [
            'username' => 'required|alpha_dash|unique:App\Models\SysAdmin\User,username',
            'password' => ['required', app()->isLocal() ? null : Password::min(8)->uncompromised()],
            'name'     => 'sometimes|required',
            'email'    => 'sometimes|required|email'
        ];
    }

    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');

            return;
        }


        if ($command->option('autoPassword')) {
            $password = (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true));
        } else {
            $password = $command->secret('What is the password?');
        }

        $this->setRawAttributes(
            [
                'username' => $command->argument('username'),
                'password' => $password,
                'name'     => $command->argument('name'),
            ]
        );

        if ($command->option('email')) {
            $this->fill(['email' => $command->option('email')]);
        }


        $validatedData = $this->validateAttributes();


        $res = $this->handle($organisation, $validatedData);
        /** @var User $user */
        $user = $res->model;

        setPermissionsTeamId($organisation->id);

        foreach ($command->option('role') as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $user->assignRole($role->name);
            } else {
                $command->error("Role $roleName not found");
            }
        }


        $command->line("Guest user created $user->username");

        $command->table(
            ['Username', 'Password', 'Email', 'Name', 'Roles'],
            [
                [
                    $user->username,
                    ($command->option('autoPassword') ? $password : '*****'),
                    $user->email,
                    $user->name,
                    $user->getRoleNames()->implode(',')
                ],

            ]
        );
    }


}
