<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 11 Nov 2022 11:38:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;


use App\Models\Central\Tenant;

use App\Models\Web\WebUser;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWebUserApiToken

{
    use AsAction;

    public string $commandSignature = 'web-user:token {tenant_code} {web_user_slug}';
    public string $commandDescription = 'Add api token to web user.';

    public function handle(WebUser $webUser, $tokenData): string
    {
        $token= $webUser->createToken(
            Arr::get($tokenData, 'name', 'full-access'),
            Arr::get($tokenData, 'abilities', ['*']),
        )->plainTextToken;

        HydrateWebUser::make()->tokens($webUser);
        return $token;

    }

    public function asCommand(Command $command): int
    {
        $tenant = Tenant::where('code', ($command->argument('tenant_code')))->firstOrFail();

        return $tenant->run(function () use ($command) {
            if ($webUser = WebUser::where('slug',$command->argument('web_user_slug'))->first()) {
                $token = $this->handle($webUser, []);
                $command->line("Web user access token: $token");

                return 0;
            } else {
                $command->error("WebUser not found: {$command->argument('web_user_slug')}");

                return 1;
            }
        });
    }


}
