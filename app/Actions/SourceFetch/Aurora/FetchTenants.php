<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 29 Apr 2023 14:31:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Traits\WithOrganisationSource;
use App\Models\Organisation\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTenants
{
    use AsAction;
    use WithOrganisationSource;


    public function handle(SourceOrganisationService $organisationSource, Organisation $organisation): Organisation
    {
        $organisationData = $organisationSource->fetchTenant($organisation);
        $organisation->update(
            $organisationData['tenant']
        );
        $accountsServiceProviderData = Db::connection('aurora')->table('Payment Service Provider Dimension')
            ->select('Payment Service Provider Key')
            ->where('Payment Service Provider Block', 'Accounts')->first();

        if ($accountsServiceProviderData) {
            $organisation->execute(fn (Organisation $organisation) => $organisation->accountsServiceProvider()->update(
                [
                    'source_id' => $accountsServiceProviderData->{'Payment Service Provider Key'}
                ]
            ));
        }

        return $organisation;
    }


    public string $commandSignature = 'fetch:tenants  {--d|db_suffix=}';

    public function asCommand(Command $command): int
    {
        Organisation::all()->eachCurrent(function (Organisation $organisation) use ($command) {
            $organisationSource = $this->getTenantSource($organisation);
            $organisationSource->initialisation(app('currentTenant'), $command->option('db_suffix') ?? '');
            $organisation = $this->handle($organisationSource, $organisation);
            if ($organisation->created_at->lt($organisation->group->created_at)) {
                $organisation->group->created_at = $organisation->created_at;
                $organisation->group->save();
            }
        });


        return 0;
    }


}
