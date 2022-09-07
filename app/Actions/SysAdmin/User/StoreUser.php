<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use App\Actions\StoreModelAction;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;


class StoreUser extends StoreModelAction
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'new:user {organisation_code} {username} {name} {--E|email=} {--N|name=} {--r|role=*} {--a|autoPassword}';


    public function handle(Organisation $organisation, array $modelData): ActionResult
    {
        $modelData['password'] = Hash::make($modelData['password']);

        $user = User::create($modelData);
        $organisation->users()->attach($user->id);

        $user->organisation_id = $organisation->id;
        $user->save();

        return $this->finalise($user);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|alpha_dash|unique:App\Models\Auth\User,username',
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
            }else{
                $command->error("Role $roleName not found");
            }
        }


        $command->line("Account admin created $user->username");

        $command->table(
            ['Username', 'Password', 'Email', 'Name','Roles'],
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
