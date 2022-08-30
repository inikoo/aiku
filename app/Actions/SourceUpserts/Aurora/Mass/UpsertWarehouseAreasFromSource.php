<?php /*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:33:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */
/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\SourceUpserts\Aurora\Single\UpsertWarehouseAreaFromSource;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertWarehouseAreasFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:warehouse-areas {organisation_code}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Warehouse Area Dimension')
                ->select('Warehouse Area Key')
                ->get() as $auroraData
        ) {

            UpsertWarehouseAreaFromSource::run($organisationSource, $auroraData->{'Warehouse Area Key'});
        }
    }


}
