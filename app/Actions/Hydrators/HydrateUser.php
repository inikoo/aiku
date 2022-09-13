<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 21:16:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;


use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateUser extends HydrateModel
{


    public string $commandSignature = 'hydrate:user {id?}';


    public function handle(User $user): void
    {
        $this->organisationStats($user);
    }

    public function organisationStats(User $user)
    {
        $numberOrganisations = DB::table('organisation_user')->where('user_id', $user->id)->where('status', true)
            ->count();


        $current_ui_organisation_id = $user->current_ui_organisation_id;
        if ($numberOrganisations == 0) {
            $current_ui_organisation_id = null;
        } elseif (
            !$user->current_ui_organisation_id  or
            !$user->organisations->pluck('pivot.organisation_id')->contains($user->current_ui_organisation_id)) {
            $current_ui_organisation_id = $user->organisations->first()->pivot->organisation_id;
        }
        $user->update(
            [
                'number_organisations'       => $numberOrganisations,
                'current_ui_organisation_id' => $current_ui_organisation_id
            ]
        );
    }

    protected function getModel(int $id): User
    {
        return User::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return User::get();
    }

    public function asCommand(Command $command): void
    {
        if ($command->argument('id')) {
            $this->handle($this->getModel($command->argument('id')));
            $command->info('Done!');
        } else {
            $this->loopAll($command);
        }
    }

}


