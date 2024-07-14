<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Models\CRM\WebUser;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateWebUserApiToken
{
    use AsAction;

    public string $commandSignature   = 'web-user:token {web_user_slug}';
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

        try {
            $webUser=WebUser::where('slug', $command->argument('web_user_slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('WebUser not found');
            return 1;
        }

        $token = $this->handle($webUser, []);
        $command->line("Web user access token: $token");

        return 0;


    }
}
