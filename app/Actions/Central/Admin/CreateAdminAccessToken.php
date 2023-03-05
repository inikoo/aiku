<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 12:55:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Admin;

use App\Models\Central\Admin;
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
