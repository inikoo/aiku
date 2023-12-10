<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Enums\Helpers\Avatars\DiceBearStylesEnum;
use App\Models\SysAdmin\Group;
use App\Models\Media\Media;
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
            $media = $group->addMediaFromString(GetDiceBearAvatar::run(DiceBearStylesEnum::SHAPES, $seed))
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
