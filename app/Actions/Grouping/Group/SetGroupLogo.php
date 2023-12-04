<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 16:25:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Group;

use App\Models\Media\Media;
use App\Models\Grouping\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetGroupLogo
{
    use AsAction;

    public function handle(Group $group): array
    {

        try {
            $seed       = 'group-'.$group->id;
            /** @var Media $media */
            $media      = $group->addMediaFromUrl("https://api.dicebear.com/6.x/shapes/svg?seed=$seed")
                ->preservingOriginal()
                ->withProperties(
                    [
                        'group_id' => $group->id
                    ]
                )
                ->usingFileName($group->slug."-logo.sgv")
                ->toMediaCollection('logo');

            $logoId = $media->id;

            $group->update(['logo_id' => $logoId]);
            return ['result' => 'success'];
        } catch(Exception $e) {
            return ['result' => 'error', 'message' => $e->getMessage()];
        }
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
