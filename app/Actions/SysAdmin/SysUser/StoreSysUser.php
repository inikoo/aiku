<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 17:20:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\SysUser;

use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Central\CentralDomain;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\SysUser;
use App\Models\Tenancy\Tenant;
use App\Rules\AlphaDashDot;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\Console\Command\Command as CommandAlias;

class StoreSysUser
{
    use AsAction;
    use WithAttributes;


    public function handle(Admin|Tenant|CentralDomain $userable, array $userData): SysUser
    {
        if (empty($userData['language_id'])) {
            $language                = Language::where('code', config('app.locale'))->firstOrFail();
            $userData['language_id'] = $language->id;
        }

        if (empty($userData['timezone_id'])) {
            $timezone                = Timezone::where('name', config('app.timezone'))->firstOrFail();
            $userData['timezone_id'] = $timezone->id;
        }

        /** @var \App\Models\SysAdmin\SysUser $sysUser */
        $sysUser = $userable->sysUser()->create($userData);

        return $sysUser;
    }

    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username'],
            'password' => ['required', app()->isLocal() ? null : Password::min(8)->uncompromised()],

        ];
    }

    public function asAction(Admin|Tenant|CentralDomain $userable, $modelData): SysUser
    {
        $this->fill($modelData);
        $validatedData = $this->validateAttributes();
        return $this->handle($userable, $validatedData);
    }


    public string $commandSignature = 'create:system-user
    {userable : type of userable model,Admin|Tenant|CentralDomain }
    {userable_slug : userable model slug/code}
    {--u|username= : use instead of slug/code argument}
    {--a|autoPassword : generate random password}';

    public function getCommandDescription(): string
    {
        return 'Create system user.';
    }

    public function asCommand(Command $command): int
    {
        try {
            $userable = match ($command->argument('userable')) {
                'Admin'         => Admin::findOrFail($command->argument('userable_slug')),
                'Tenant'        => Tenant::where('code', $command->argument('userable_slug'))->firstOrFail(),
                'CentralDomain' => CentralDomain::findOrFail($command->argument('userable_slug')),
                default         => null
            };
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return CommandAlias::FAILURE;
        }
        if (!$userable) {
            $command->error('Invalid userable value');

            return CommandAlias::FAILURE;
        }

        if ($command->option('username')) {
            $username = $command->option('username');
        } else {
            $username = $userable->code;
        }

        if ($command->option('autoPassword')) {
            $password = (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true));
        } else {
            $password = $command->ask('What is the password?');
        }

        $this->fill([
            'username' => $username,
            'password' => $password,
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $sysUser = $this->handle($userable, $validatedData);
        $command->line("Account admin created $sysUser->username");

        $command->table(
            ['Model','Id', 'Username', 'Password'],
            [
                [
                    class_basename($userable).
                    $command->argument('userable_slug'),
                    $sysUser->username,
                    ($command->option('autoPassword') ? Arr::get($validatedData, 'password') : '*****'),
                ],

            ]
        );



        return 0;
    }
}
