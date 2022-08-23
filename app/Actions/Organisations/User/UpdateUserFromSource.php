<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 16:37:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\User;

use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpdateUserFromSource
{
    use AsAction;
    public string $commandSignature = 'fetch:user {user_id} {scopes?*}';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     * @uses updateProfileImage
     */
    #[NoReturn] public function handle(User $user, array|null $scopes=null): void
    {

        $organisationSource = app(SourceOrganisationManager::class)->make($user->organisation->type);
        $organisationSource->initialisation($user->organisation);

        $userData=$organisationSource->fetchUser(Arr::get($user->data,'source_id'));

        if($scopes==null){
            $scopes=['ProfileImage'];
        }

        foreach($scopes as $scope){
            $updateMethod = 'update' .$scope;
            if (!method_exists($this, $updateMethod)) {
                throw new Exception("Scope $scope is not supported");
            }
            $this->{$updateMethod}($userData);
        }



    }

    protected function updateProfileImage($userData){

    }

    public function asCommand(Command $command): void
    {

        $user=User::find($command->argument('user_id'));
        if(!$user){
            $command->error('User not found');
            return;
        }
        if(!$user->organisation){
            $command->error('User is not attached to any organisation');
            return;
        }

        $this->handle($user);

    }


}
