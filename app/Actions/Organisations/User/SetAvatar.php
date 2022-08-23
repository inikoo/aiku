<?php /** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 29 Mar 2022 00:42:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\User;

use App\Actions\WithUpdate;
use App\Models\Organisations\User;
use App\Models\Utils\ActionResult;
use Illuminate\Console\Command;
use Laravolt\Avatar\Avatar;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property User $user
 */
class SetAvatar
{
    use AsAction;
    use WithUpdate;

    public string $commandSignature = 'maintenance:reset-user-avatar {user_id}';


    public function handle(User $user): ActionResult
    {
        $res = new ActionResult();

        if($mediaAvatar=$user->getFirstMedia('profile')){
            $mediaAvatar->delete();
        }


        $avatar = new Avatar( config('avatar'));


        $user->update(
            [
                'data->profile_url'    => $avatar->create($user->name??'??')->toBase64(),
                'data->profile_source' => 'Avatar'
            ]
        );
        $res->changes  = array_merge($res->changes, $user->getChanges());
        $res->model    = $user;
        $res->model_id = $user->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';

        return $res;
    }


    public function asCommand(Command $command): void
    {
        $user = User::find($command->argument('user_id'));
        if (!$user) {
            $command->error('User not found');

            return;
        }

        $this->handle($user);
    }


}
