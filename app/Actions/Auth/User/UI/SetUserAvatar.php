<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 15:17:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Models\Auth\User;
use App\Models\Media\Media;
use App\Models\Organisation\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetUserAvatar
{
    use AsAction;

    public function handle(User $user): User
    {
        try {
            $seed = $user->id;
            /** @var Media $media */
            $media = $user->addMediaFromUrl("https://avatars.dicebear.com/api/identicon/$seed.svg")
                ->preservingOriginal()
                ->usingFileName($user->username."-avatar.sgv")
                ->toMediaCollection('profile', 'group');

            $avatarID = $media->id;

            $user->update(['avatar_id' => $avatarID]);
        } catch(Exception) {
            //
        }
        return $user;
    }


    public string $commandSignature = 'maintenance:reset-central-user-avatar {group : Group slug} {username : User username}';

    public function asCommand(Command $command): int
    {

        try {
            $group=Group::where('slug', $command->argument('group'))->firstOrFail();
        } catch (Exception) {
            $command->error('Group not found');
            return 1;
        }

        $group->owner->makeCurrent();

        $user = User::where('username', $command->argument('username'))->first();
        if (!$user) {
            $command->error('User not found');
            return 1;
        } else {
            $this->handle($user);
        }


        return 0;
    }
}
