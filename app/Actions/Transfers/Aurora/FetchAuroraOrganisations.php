<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 29 Apr 2023 14:31:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\Traits\WithOrganisationSource;
use App\Actions\Transfers\WithSaveMigrationHistory;
use App\Models\SysAdmin\Organisation;
use App\Transfers\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraOrganisations
{
    use AsAction;
    use WithOrganisationSource;
    use WithSaveMigrationHistory;


    public function handle(SourceOrganisationService $organisationSource, Organisation $organisation): Organisation
    {
        $organisationData = $organisationSource->fetchOrganisation($organisation);



        $organisation = UpdateOrganisation::make()->action(
            organisation: $organisation,
            modelData: $organisationData['organisation'],
            hydratorsDelay: 60,
            strict: false,
            audit: false
        );
        Organisation::enableAuditing();


        if (!$organisation->fetched_at) {
            $organisation->updateQuietly(
                [
                    'fetched_at' => now()
                ]
            );

            $this->saveMigrationHistory(
                $organisation,
                Arr::except($organisationData['organisation'], ['source','source_id','fetched_at','last_fetched_at'])
            );

        }



        $sourceData = explode(':', $organisation->source_id);
        DB::connection('aurora')->table('Account Dimension')
            ->where('Account Key', $sourceData[1])
            ->update(['aiku_id' => $organisation->id]);

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


    public string $commandSignature = 'fetch:aurora-organisations  {--d|db_suffix=}';

    /**
     * @throws \Exception
     */
    public function asCommand(Command $command): int
    {
        foreach (Organisation::all() as $organisation) {
            if ($organisation->source['type'] !== 'Aurora') {
                continue;
            }
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
