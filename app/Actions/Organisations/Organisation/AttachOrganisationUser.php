<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 22 Aug 2022 14:50:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Organisation;


use App\Models\SysAdmin\User;
use App\Models\Organisations\Organisation;
use App\Models\Organisations\OrganisationUser;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachOrganisationUser
{
    use AsAction;

    public string $commandSignature = 'org:attach-user {organisation_id} {user_id}';
    public string $commandDescription = 'Attach user to organisation';

    public function handle(Organisation $organisation, User $user)
    {
        $organisation->users()->attach($user->id);
    }


    public function asCommand(Command $command): void
    {

        if( $organisationUser=OrganisationUser::where('organisation_id',$command->argument('organisation_id'))
            ->where('user_id',$command->argument('user_id'))->first()){

            $command->error('Pair already attached!');
            return;
        }

        $this->handle(
            Organisation::findOrFail($command->argument('organisation_id')),
            User::findOrFail($command->argument('user_id'))
        );

        $command->info('Done!');
    }

}
