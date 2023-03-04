<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 16 Nov 2022 20:53:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\Central\Tenant;


use App\Actions\SysAdmin\AdminUser\StoreAdminUser;
use App\Actions\WithTenantsOption;
use App\Models\Central\Tenant;
use Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTenantAdminUser
{
    use AsAction;
    use WithTenantsOption;

    public string $commandSignature = 'create:tenant-admin-user {--tenant=*}  {--e|email=} {--a|abilities= : json array with string name, array abilities}   ';

    public function getCommandDescription(): string
    {
        return 'Create admin-user for tenant';
    }

    public function handle(Tenant $tenant,array $adminUserData, ?array $tokenData = null): object
    {

        $token = null;


        $password = Arr::get(
            $adminUserData,
            'password',
            (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
        );


        $adminUser = StoreAdminUser::run(
            $tenant,
            [
                'username' => Arr::get($adminUserData, 'username', $tenant->code),
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
            'password' => $password,
            'token' => $token,
            'adminUser' => $adminUser
        ];
    }



    public function asCommand(Command $command): int
    {
        $tenants = $this->getTenants($command);



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


        foreach($tenants as $tenant){
            $result = $this->handle(
                $tenant,
                $adminUserData,
                $tokenData
            );

            if ($result->adminUser) {
                $command->table(
                    ['AdminUser', 'Token'],
                    [
                        [
                            $result->adminUser->username,
                            $result->token
                        ],

                    ]
                );


            }
        }



        return 0;
    }


}
