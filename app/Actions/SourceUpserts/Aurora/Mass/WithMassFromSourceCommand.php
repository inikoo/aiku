<?php
/** @noinspection PhpUnused */
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:37:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\Organisation;
use Illuminate\Console\Command;

trait WithMassFromSourceCommand{

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');
            return;
        }
        $organisationSource = app(SourceOrganisationManager::class)->make($organisation->type);
        $organisationSource->initialisation($organisation);


        $this->handle($organisationSource);
    }

}

