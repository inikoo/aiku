<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 29 Sept 2022 11:28:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Actions\Auth\User\StoreUser;
use App\Enums\Auth\User\UserAuthTypeEnum;
use App\Models\Auth\User;
use App\Models\HumanResources\Employee;
use App\Models\Organisation\Organisation;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

/**
 * @property string $newPassword
 */
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
        $this->newPassword = $password ?? (app()->isProduction() ? wordwrap(Str::random(), 4, '-', true) : 'hello');
        $modelData         = [
            'username'    => $username ?? $employee->slug,
            'password'    => $this->newPassword,
            'contact_name'=> $employee->contact_name,
            'email'       => $employee->work_email,
            'auth_type'   => UserAuthTypeEnum::DEFAULT
        ];


        $user = StoreUser::run($employee, $modelData);
        foreach ($employee->jobPositions as $jobPosition) {
            $user->assignJoBPositionRoles($jobPosition);
        }

        return $user;
    }

    public function asController(Employee $employee): User
    {
        if ($employee->user) {
            return $employee->user;
        }

        return $this->handle($employee);
    }

    public function HtmlResponse(User $user): RedirectResponse
    {
        /** @var Employee $employee */
        $employee = $user->parent;

        return Redirect::route('grp.hr.employees.show', $employee->id)->with('notification', [
            'type'   => 'newUser',
            'message'=> __('New user created'),
            'fields' => [
                'username' => [
                    'label' => __('username'),
                    'value' => $user->username
                ],
                'password' => [
                    'label' => __('password'),
                    'value' => $this->newPassword
                ]
            ]

        ]);
    }

    public function asCommand(Command $command): int
    {
        $organisation = Organisation::where('slug', $command->argument('tenant'))->first();
        if (!$organisation) {
            $command->error("Organisation ".$command->argument('tenant')." not found");

            return 1;
        }


        return (int)$organisation->execute(
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
                            $employee->contact_name,
                            $user->getRoleNames()->implode(',')
                        ],

                    ]
                );

                return 0;
            }
        );
    }
}
