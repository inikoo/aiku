<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 15:17:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User\UI;

use App\Models\Auth\User;
use App\Models\Media\Media;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetUserAvatar
{
    use AsAction;

    public function handle(User $user): array
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
            $user->update(['avatar_id' => $avatarID]);

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
