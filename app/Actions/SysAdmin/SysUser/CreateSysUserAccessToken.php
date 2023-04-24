<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:11:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\SysUser;

use App\Models\SysAdmin\SysUser;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateSysUserAccessToken
{
    use asCommand;
    use WithAttributes;

    public function handle(SysUser $sysUser, string $tokenName, array $scopes): string
    {
        return $sysUser->createToken($tokenName, $scopes)->plainTextToken;
    }

    public string $commandSignature = 'access-token:sys-user
    {username : sys user username}
    {token_name}
    {scopes?*}';

    public function getCommandDescription(): string
    {
        return 'Create system user token.';
    }


    public function asCommand(Command $command): int
    {
        try {
            $sysUser = SysUser::where('username', $command->argument('username'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        $token = $this->handle($sysUser, $command->argument('token_name'), $command->argument('scopes'));
        $command->line("Admin access token: $token");

        return 0;
    }
}
