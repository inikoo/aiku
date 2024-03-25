<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 29 Apr 2023 14:31:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\Traits\WithOrganisationSource;
use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchOrganisations
{
    use AsAction;
    use WithOrganisationSource;


    public function handle(SourceOrganisationService $organisationSource, Organisation $organisation): Organisation
    {
        $organisationData = $organisationSource->fetchOrganisation($organisation);

        $organisation=UpdateOrganisation::run($organisation, $organisationData['organisation']);

        $accountsServiceProviderData = Db::connection('aurora')->table('Payment Service Provider Dimension')
            ->select('Payment Service Provider Key')
            ->where('Payment Service Provider Block', 'Accounts')->first();


        if ($accountsServiceProviderData) {
            $organisation->accountsServiceProvider()->update(
                [
                    'source_id' => $organisation->id.':'.$accountsServiceProviderData->{'Payment Service Provider Key'}
                ]
            );
        }

        return $organisation;
    }


    public string $commandSignature = 'fetch:organisations  {--d|db_suffix=}';

    /**
     * @throws \Exception
     */
    public function asCommand(Command $command): int
    {
        foreach (Organisation::all() as $organisation) {
            $organisationSource = $this->getOrganisationSource($organisation);
            $organisationSource->initialisation($organisation, $command->option('db_suffix') ?? '');
            $organisation = $this->handle($organisationSource, $organisation);
            if ($organisation->created_at->lt($organisation->group->created_at)) {
                $organisation->group->created_at = $organisation->created_at;
                $organisation->group->save();
            }
        }

        return 0;
    }
}
