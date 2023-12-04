<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Actions\Traits\WithOrganisationsArgument;
use App\Models\SysAdmin\Organisation;
use Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrganisationAdminUser
{
    use AsAction;
    use WithOrganisationsArgument;

    public string $commandSignature = 'create:organisation-admin-user {organisations?*}  {--e|email=} {--a|abilities= : json array with string name, array abilities}   ';

    public function getCommandDescription(): string
    {
        return 'Create admin-user for organisation';
    }

    public function handle(Organisation $organisation, array $adminUserData, ?array $tokenData = null): object
    {
        $token = null;


        $password = Arr::get(
            $adminUserData,
            'password',
            (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
        );


        $adminUser = StoreSysUser::run(
            $organisation,
            [
                'username' => Arr::get($adminUserData, 'username', $organisation->slug),
                'email'    => Arr::get($adminUserData, 'email'),
                'password' => $password
            ]
        );

        if ($tokenData) {
            $token = $adminUser->createToken(
                Arr::get($tokenData, 'name'),
                Arr::get($tokenData, 'abilities')
            )->plainTextToken;
        }

        return (object)[
            'password'  => $password,
            'token'     => $token,
            'adminUser' => $adminUser
        ];
    }



    public function asCommand(Command $command): int
    {
        $organisations = $this->getOrganisations($command);

        $adminUserData = [];
        $tokenData     = null;
        if ($command->option('email')) {
            $adminUserData['email'] = $command->option('email');
        }

        if ($command->option('abilities')) {
            $tokenData = json_decode($command->option('abilities'), true);

            if (!$tokenData) {
                $command->error('Invalid json in abilities option');

                return 1;
            }
        }


        foreach ($organisations as $organisation) {
            $result = $this->handle(
                $organisation,
                $adminUserData,
                $tokenData
            );

            if ($result->sysUser) {
                $command->table(
                    ['SysUser', 'Token'],
                    [
                        [
                            $result->sysUser->username,
                            $result->token
                        ],

                    ]
                );
            }
        }



        return 0;
    }
}
