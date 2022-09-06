<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 22 Aug 2022 14:50:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Organisation;


use App\Models\Organisations\OrganisationUser;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachOrganisationUser
{
    use AsAction;

    public string $commandSignature =  'org:detach-user {organisation_id} {user_id}';
    public string $commandDescription ='Detach user to organisation';

    public function handle(OrganisationUser $organisationUser)
    {

        $organisationUser->delete();
    }

    public function asCommand(Command $command): void
    {


        if( $organisationUser=OrganisationUser::where('organisation_id',$command->argument('organisation_id'))
            ->where('user_id',$command->argument('user_id'))->first()){
            $this->handle($organisationUser);
            $command->info('Done!');
        }else{
            $command->error('Pair not attached!');
        }





    }


}
