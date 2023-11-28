<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 01 May 2023 16:35:18 Malaysia Time, KLIA2, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Organisation\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UserAddRoles
{
    use AsAction;
    use WithActionUpdate;

    private bool $trusted = false;

    public function handle(User $user, array $roles): User
    {


        foreach ($roles as $role) {
            $user->assignDirectRole($role);
        }

        return $user;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->trusted) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }


    public function rules(): array
    {
        return [
            'role_names' => ['required', 'array'],

        ];
    }

    public function afterValidator(Validator $validator): void
    {

        $roles = [];
        foreach ($this->get('role_names') as $roleName) {
            /** @var Role $role */
            if ($role = Role::where('name', $roleName)->first()) {
                $roles[] = $role;
            } else {
                $validator->errors()->add('roles', "Role $roleName not found");
            }
        }


        $this->set('roles', $roles);


    }


    public function asController(User $user, ActionRequest $request): User
    {
        return $this->handle($user, $request->validated());
    }

    public function action(User $user, array $roleNames): User
    {
        $this->trusted = true;
        $this->setRawAttributes(
            [
                'role_names' => $roleNames
            ]
        );
        $this->validateAttributes();

        return $this->handle($user, $this->get('roles'));
    }

    public string $commandSignature = 'user:add-roles {tenant : tenant slug} {user : User username} {roles* : list of roles}';


    public function asCommand(Command $command): int
    {
        $this->trusted = true;


        try {
            $organisation = Organisation::where('slug', $command->argument('tenant'))->firstOrFail();
        } catch (Exception) {
            $command->error("Organisation {$command->argument('tenant')} not found");

            return 1;
        }




        try {
            $user = User::where('username', $command->argument('user'))->firstOrFail();
        } catch (Exception) {
            $command->error("User {$command->argument('user')} not found");

            return 1;
        }


        $this->fill([
            'role_names' => $command->argument('roles'),
        ]);

        $this->validateAttributes();
        $groupUser = $this->handle($user, $this->get('roles'));


        $command->info("Group User <fg=yellow>$groupUser->username</> added roles: ".join($command->argument('roles'))." 👍");

        return 0;
    }


}
