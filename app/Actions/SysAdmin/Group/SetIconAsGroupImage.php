<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Media\Media\StoreMediaFromIcon;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Models\SysAdmin\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetIconAsGroupImage
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Group $group): Group
    {
        $media = StoreMediaFromIcon::run($group);
        $this->attachMediaToModel($group, $media, 'logo');
        return $group;
    }


    public string $commandSignature = 'group:logo {group : Group slug}';

    public function asCommand(Command $command): int
    {

        try {
            $group=Group::where('slug', $command->argument('group'))->firstOrFail();
        } catch (Exception) {
            $command->error('Group not found');
            return 1;
        }


        $result=$this->handle($group);
        if($result['result']==='success') {
            $command->info('Logo set');
            return 0;
        } else {
            $command->error($result['message']);
            return 1;
        }
    }
}
