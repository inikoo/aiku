<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:05:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Console\Commands\Admin;

use App\Models\Admin\Admin;
use Illuminate\Console\Command;


class CreateAdminAccessToken extends Command
{

    protected $signature = 'admin:token {slug} {token_name} {scopes?*} ';

    protected $description = 'Create new admin access token';


    public function handle(): int
    {
        if ($admin = Admin::firstWhere('slug', $this->argument('slug'))) {

            $token= $admin->adminUser->createToken($this->argument('token_name'),$this->argument('scopes'))->plainTextToken;
            $this->line("Admin access token: $token");
        } else {
            $this->error("Admin not found: {$this->argument('slug')}");
        }

        return 0;
    }
}
