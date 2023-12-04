<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Models\Media\Media;
use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetUserAvatar
{
    use AsAction;

    public function handle(User $user, bool $saveHistory = true): array
    {
        try {
            /** @var Media $media */
            $media = $user->addMediaFromUrl("https://api.dicebear.com/7.x/identicon/svg?seed=".$user->slug)
                ->preservingOriginal()
                ->withProperties(
                    [
                        'group_id' => $user->group_id
                    ]
                )
                ->usingName($user->slug."-avatar")
                ->usingFileName($user->slug."-avatar.sgv")
                ->toMediaCollection('avatar');

            $avatarID = $media->id;

            if($saveHistory) {
                $user->update(['avatar_id' => $avatarID]);
            } else {
                $user->updateQuietly(['avatar_id' => $avatarID]);
            }



            return ['result' => 'success'];
        } catch (Exception $e) {
            return ['result' => 'error', 'message' => $e->getMessage()];
        }
    }


    public string $commandSignature = 'user:avatar {slug : User slug}';

    public function asCommand(Command $command): int
    {
        try {
            $user = User::where('slug', $command->argument('slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('User not found');

            return 1;
        }

        $result = $this->handle($user);

        if ($result['result'] === 'success') {
            $command->info('Avatar set');

            return 0;
        } else {
            $command->error($result['message']);

            return 1;
        }
    }
}
