<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 20:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\GroupUser;

use App\Models\Auth\GroupUser;
use App\Models\Central\CentralMedia;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetGroupUserAvatar
{
    use AsAction;

    public string $commandSignature = 'maintenance:reset-central-user-avatar {username}';



    public function handle(GroupUser $centralUser): GroupUser
    {
        try {
            $seed = $centralUser->id;
            /** @var CentralMedia $centralMedia */
            $centralMedia = $centralUser->addMediaFromUrl("https://avatars.dicebear.com/api/identicon/$seed.svg")
                ->preservingOriginal()
                ->usingFileName($centralUser->username."-avatar.sgv")
                ->toMediaCollection('profile', 'local');

            $avatarID = $centralMedia->id;

            $centralUser->update(['media_id' => $avatarID]);
        } catch(Exception) {
            //
        }
        return $centralUser;
    }



    public function asCommand(Command $command): int
    {
        $centralUser = GroupUser::where('username', $command->argument('username'))->first();
        if (!$centralUser) {
            $command->error('User not found');
            return 1;
        } else {
            $this->handle($centralUser);
        }


        return 0;
    }
}
