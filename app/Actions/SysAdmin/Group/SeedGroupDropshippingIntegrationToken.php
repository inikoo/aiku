<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 12:29:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Models\SysAdmin\Group;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedGroupDropshippingIntegrationToken
{
    use AsAction;

    public function handle(Group $group, string $token): Group
    {
        $group->update(['dropshipping_integration_token' => $group->id.':'.$token]);
        return $group;
    }


    public string $commandSignature = 'group:seed-integration-token {slug : The slug of the group} {token?} ';


    public function asCommand(Command $command): int
    {

        try {
            $group = Group::where('slug', $command->argument('slug'))->firstOrFail();
        } catch (Exception $exception) {
            $command->error('Group not found');
            return 1;
        }

        if(!$command->argument('token')) {
            $token= bin2hex(Str::random(32));
        } else {
            $token = $command->argument('token');
        }

        $group=$this->handle($group, $token);

        $command->info('Token: '.$group->dropshipping_integration_token);

        return 0;
    }

}
