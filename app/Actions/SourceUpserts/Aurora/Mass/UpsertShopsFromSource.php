<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:36:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\SourceUpserts\Aurora\Single\UpsertShopFromSource;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;



class UpsertShopsFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:shops {organisation_code} {scopes?*}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Store Dimension')
                ->select('Store Key')
                ->whereIn('Store Status', ['Normal', 'ClosingDown'])
                ->get() as $auroraData
        ) {

            UpsertShopFromSource::run($organisationSource, $auroraData->{'Store Key'});
        }
    }


}
