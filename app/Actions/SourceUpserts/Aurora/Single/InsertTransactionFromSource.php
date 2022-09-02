<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:22:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Sales\Transaction\StoreTransaction;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class InsertTransactionFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id, Order $order): ?Transaction
    {
        if ($transactionData = $organisationSource->fetchTransaction(type: 'HistoricProduct', id: $organisation_source_id)) {

            if (!Transaction::where('organisation_source_id', $transactionData['transaction']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = StoreTransaction::run(
                    order:     $order,
                    modelData: $transactionData['transaction']
                );

                return $res->model;
            }
        }


        return null;
    }


}
