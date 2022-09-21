<?php
/*
*  Author: Raul Perusquia <raul@inikoo.com>
*  Created: Tue, 06 Sept 2022 15:34:51 Malaysia Time, Kuala Lumpur, Malaysia
*  Copyright (c) 2022, Raul A Perusquia Flores
*/

/** @noinspection PhpUnused */

namespace App\Actions\SysAdmin\User;

use App\Actions\WithTenantsArgument;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Laravolt\Avatar\Avatar;
use Lorisleiva\Actions\Concerns\AsAction;


class SetAvatar
{
    use AsAction;
    use WithTenantsArgument;

    public string $commandSignature = 'maintenance:reset-user-avatar {tenants} {user_id}';


    public function handle(User $user): User
    {
        if ($mediaAvatar = $user->getFirstMedia('profile')) {
            $mediaAvatar->delete();
        }

        $avatar = new Avatar(config('avatar'));
        $user->update(
            [
                'data->profile_url'    => $avatar->create($user->name ?? '??')->toBase64(),
                'data->profile_source' => 'Avatar'
            ]
        );


        return $user;
    }


    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->run(function () use ($command) {
                $user = User::find($command->argument('user_id'));
                if (!$user) {
                    $command->error('User not found');
                } else {
                    $this->handle($user);
                }
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


}
