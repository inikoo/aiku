<?php /*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:35:31 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */ /** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\Organisations\User\SetAvatarFromImage;
use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property \App\Models\Organisations\User $user
 * @property \App\Models\Organisations\Organisation|null $organisation
 */
class UpdateUserFromSource
{
    use AsAction;

    public string $commandSignature = 'source-update:user {user_id} {scopes?*}';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     * @uses updateProfileImage
     */
    #[NoReturn] public function handle(User $user, array|null $scopes=null): void
    {

        $this->user=$user;
        // todo get organisation id from arguments (user case: user below multiple organisations)
        $this->organisation=$user->organisation;

        $validScopes=['ProfileImage','Permissions'];

        $organisationSource = app(SourceOrganisationManager::class)->make($this->organisation->type);
        $organisationSource->initialisation($this->organisation);

        $userData=$organisationSource->fetchUser(Arr::get($user->data,'source_id'));

        if($scopes==null){
            $scopes=$validScopes;
        }

        foreach($scopes as $scope){
            $updateMethod = 'update' .$scope;
            if (!method_exists($this, $updateMethod)) {
                throw new Exception("Scope $scope is not supported");
            }
            $this->{$updateMethod}($userData);
        }



    }

    protected function updatePermissions($userData): void
    {
        $roles=$userData['roles']??[];
        setPermissionsTeamId($this->organisation->id);
        $this->user->syncRoles($roles);



    }

    protected function updateProfileImage($userData): void
    {

        foreach($userData['profile_images']??[] as $profileImage){
            SetAvatarFromImage::run($this->user, $profileImage['image_path'], $profileImage['filename']);
        }

    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
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
