<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:34:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Mass;


use App\Actions\SourceUpserts\Aurora\Single\UpsertDeliveryNoteFromSource;

use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertDeliveryNotesFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:delivery_notes {organisation_code}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Delivery Note Dimension')
                ->select('Delivery Note Key')
                ->whereIn(
                    'Delivery Note State',
                    ['Ready to be Picked', 'Picker Assigned', 'Picking', 'Picked', 'Packing', 'Packed', 'Packed Done', 'Approved']
                )
                ->get() as $auroraData
        ) {
            UpsertDeliveryNoteFromSource::run($organisationSource, $auroraData->{'Delivery Note Key'});
        }
    }


}
