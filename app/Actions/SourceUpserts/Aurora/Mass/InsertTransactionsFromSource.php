<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 21:10:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceUpserts\Aurora\Mass;


use App\Actions\SourceUpserts\Aurora\Single\InsertTransactionFromSource;
use App\Models\Sales\Order;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class InsertTransactionsFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, Order $order): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Key', $order->organisation_source_id)
                ->get() as $auroraData
        ) {
            InsertTransactionFromSource::run($organisationSource, $auroraData->{'Order Transaction Fact Key'},$order);
        }
    }


}
