<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 04 Sept 2022 12:06:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */
/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\SourceUpserts\Aurora\Single\UpsertStockFromSource;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class UpsertStocksFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:stocks {organisation_code} {scopes?*}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Part Dimension')
                ->select('Part SKU')
                ->where('Part Status', '!=','Not In Use')
                ->get() as $auroraData
        ) {

            UpsertStockFromSource::run($organisationSource, $auroraData->{'Part SKU'});
        }
    }


}
