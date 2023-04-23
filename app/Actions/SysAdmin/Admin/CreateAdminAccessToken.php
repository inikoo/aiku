<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:11:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Admin;

use App\Models\SysAdmin\Admin;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateAdminAccessToken
{
    use asCommand;
    use WithAttributes;


    public string $commandSignature = 'create:admin-token
    {code : admin code}
    {token_name}
    {scopes?*}';

    public function getCommandDescription(): string
    {
        return 'Create new admin access token.';
    }


    public function asCommand(Command $command): int
    {
        if ($admin = Admin::firstWhere('code', $command->argument('code'))) {
            $token = $admin->adminUser->createToken($command->argument('token_name'), $command->argument('scopes'))->plainTextToken;
            $command->line("Admin access token: $token");
            return 0;
        } else {
            $command->error("Admin not found: {$command->argument('code')}");

            return 1;
        }
    }
}
