<?php
/*
*  Author: Raul Perusquia <raul@inikoo.com>
*  Created: Tue, 06 Sept 2022 15:34:51 Malaysia Time, Kuala Lumpur, Malaysia
*  Copyright (c) 2022, Raul A Perusquia Flores
*/


namespace App\Actions\SysAdmin\User;

use App\Actions\WithTenantsArgument;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Console\Command;
use Laravolt\Avatar\Avatar;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\TemporaryDirectory\TemporaryDirectory;


class SetAvatar
{
    use AsAction;
    use WithTenantsArgument;

    public string $commandSignature = 'maintenance:reset-user-avatar {tenant_code} {user_id}';


    /**
     * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(User $user): User
    {
        try {
            $seed=$user->global_id;
            $user->addMediaFromUrl("https://avatars.dicebear.com/api/identicon/$seed.svg")
                ->preservingOriginal()
                ->toMediaCollection('profile');
        } catch (Exception) {
            $temporaryDirectory = (new TemporaryDirectory())->create();

            $image_path = $temporaryDirectory->path('avatar.png');
            /** @var Employee|Guest $parent */
            $parent=$user->parent;
            (new Avatar)->create($parent->name)->save($image_path);

            $checksum = md5_file($image_path);

            if ($user->getMedia('profile', ['checksum' => $checksum])->count() == 0) {
                $user->addMedia($image_path)
                    ->preservingOriginal()
                    ->withCustomProperties(['checksum' => $checksum])
                    ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                    ->toMediaCollection('profile');
            }
        }


        $user->refresh();

        return $user;
    }


    public function asCommand(Command $command): int
    {
        $tenant = Tenant::firstWhere('code', $command->argument('tenant_code'));
        if (!$tenant) {
            $command->error('User not found');

            return 1;
        }
        $exitCode = 0;


        $result = (int)$tenant->execute(
        /**
         * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
         * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
         * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
         */ function () use ($command) {
                $user = User::find($command->argument('user_id'));
                if (!$user) {
                    $command->error('User not found');
                } else {
                    $this->handle($user);
                }
            }
        );

        if ($result !== 0) {
            $exitCode = $result;
        }


        return $exitCode;
    }


}
