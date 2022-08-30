<?php /** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:27:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\SourceUpserts\Aurora\Single\UpsertWarehouseFromSource;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertWarehousesFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:warehouses {organisation_code}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Warehouse Dimension')
                ->select('Warehouse Key')
                ->get() as $auroraData
        ) {

            UpsertWarehouseFromSource::run($organisationSource, $auroraData->{'Warehouse Key'});
        }
    }


}
