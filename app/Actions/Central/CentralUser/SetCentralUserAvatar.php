<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Mar 2023 14:11:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralUser;

use App\Models\Central\CentralMedia;
use App\Models\Central\CentralUser;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class SetCentralUserAvatar
{
    use AsAction;

    public string $commandSignature = 'maintenance:reset-central-user-avatar {username}';


    /**
     * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(CentralUser $centralUser): CentralUser
    {
        $seed = $centralUser->id;
        /** @var CentralMedia $centralMedia */
        $centralMedia = $centralUser->addMediaFromUrl("https://avatars.dicebear.com/api/identicon/$seed.svg")
            ->preservingOriginal()
            ->usingFileName($centralUser->username . "-avatar.sgv")
            ->toMediaCollection('profile', 'central');

        $avatarID = $centralMedia->id;

        $centralUser->update(['media_id' => $avatarID]);

        return $centralUser;
    }


    /**
     * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function asCommand(Command $command): int
    {
        $centralUser = CentralUser::where('username', $command->argument('username'))->first();
        if (!$centralUser) {
            $command->error('User not found');
            return 1;
        } else {
            $this->handle($centralUser);
        }


        return 0;
    }
}
