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
use Lorisleiva\Actions\Concerns\AsAction;

class SetGroupDropshippingIntegrationToken
{
    use AsAction;

    public function handle(Group $group, string $token): Group
    {
        $group->update(['dropshipping_integration_token' => $token]);
        return $group;
    }


    public string $commandSignature = 'group:seed-integration-token  {token?} ';


    public function asCommand(Command $command): int
    {

        $tokenData = explode(':', $command->argument('token'));
        if(count($tokenData)!=2) {
            $command->error('Invalid token format');
            return 1;
        }


        try {
            $group = Group::where('id', $tokenData[0])->firstOrFail();
        } catch (Exception $exception) {
            $command->error('Group not found');
            return 1;
        }

        $group=$this->handle($group, $command->argument('token'));

        $command->info('Token: '.$group->dropshipping_integration_token);

        return 0;
    }

}
