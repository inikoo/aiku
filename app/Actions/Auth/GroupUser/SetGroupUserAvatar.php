<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 20:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Models\Auth\GroupUser;
use App\Models\Media\GroupMedia;
use App\Models\Tenancy\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetGroupUserAvatar
{
    use AsAction;

    public function handle(GroupUser $groupUser): GroupUser
    {
        try {
            $seed = $groupUser->id;
            /** @var GroupMedia $groupMedia */
            $groupMedia = $groupUser->addMediaFromUrl("https://avatars.dicebear.com/api/identicon/$seed.svg")
                ->preservingOriginal()
                ->usingFileName($groupUser->username."-avatar.sgv")
                ->toMediaCollection('profile', 'group');

            $avatarID = $groupMedia->id;

            $groupUser->update(['avatar_id' => $avatarID]);
        } catch(Exception) {
            //
        }
        return $groupUser;
    }


    public string $commandSignature = 'maintenance:reset-central-user-avatar {group : Group slug} {username : GroupUser username}';

    public function asCommand(Command $command): int
    {

        try {
            $group=Group::where('slug', $command->argument('group'))->firstOrFail();
        } catch (Exception) {
            $command->error('Group not found');
            return 1;
        }

        $group->owner->makeCurrent();

        $groupUser = GroupUser::where('username', $command->argument('username'))->first();
        if (!$groupUser) {
            $command->error('GroupUser not found');
            return 1;
        } else {
            $this->handle($groupUser);
        }


        return 0;
    }
}
