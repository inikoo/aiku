<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 29 Sept 2022 11:28:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;


use App\Actions\Central\CentralUser\StoreCentralUser;
use App\Actions\SysAdmin\User\StoreUser;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateUserFromEmployee
{

    use AsAction;
    use WithAttributes;


    public string $commandSignature = 'create:user-from-employee {tenant : The tenant code} {employee : The employee identification code}';

    public function getCommandDescription(): string
    {
        return 'Create a user from an employee.';
    }


    public function handle(Employee $employee, ?string $password = null, ?string $username = null): User
    {
        $modelData = [
            'username' => $username ?? $employee->code,
            'password' => $password ?? (app()->isProduction() ? wordwrap(Str::random(), 4, '-', true) : 'hello')
        ];


        $centralUser = StoreCentralUser::run($modelData);

        /** @var User $user */
        $user = StoreUser::run(tenant(), $employee, $centralUser);
        foreach ($employee->jobPositions as $jobPosition) {
            $user->assignJoBPositionRoles($jobPosition);
        }

        return $user;
    }


    public function asCommand(Command $command): int
    {
        $tenant = Tenant::where('code', $command->argument('tenant'))->first();
        if (!$tenant) {
            $command->error("Tenant ".$command->argument('tenant')." not found");

            return 1;
        }


        return (int)$tenant->run(
            function () use ($command) {
                $employee = Employee::where('code', $command->argument('employee'))->first();
                if (!$employee) {
                    $command->error("Employee ".$command->argument('employee')." not found");

                    return 1;
                }


                if ($employee->user) {
                    $command->error("Employee already has an user");

                    return 1;
                }


                $password = (app()->isProduction() ? wordwrap(Str::random(), 4, '-', true) : 'hello');
                $user     = $this->handle($employee);


                $command->line("Employee user created $user->username");

                $command->table(
                    ['Username', 'Password', 'Name', 'Roles'],
                    [
                        [
                            $user->username,
                            $password,
                            $employee->name,
                            $user->getRoleNames()->implode(',')
                        ],

                    ]
                );

                return 0;
            }
        );
    }


}
