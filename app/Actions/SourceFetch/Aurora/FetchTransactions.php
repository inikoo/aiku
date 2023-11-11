<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\OMS\Transaction\StoreTransaction;
use App\Models\OMS\Order;
use App\Models\OMS\Transaction;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTransactions
{
    use AsAction;


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $source_id, Order $order): ?Transaction
    {
        if ($transactionData = $organisationSource->fetchTransaction(id: $source_id)) {
            if (!Transaction::where('source_id', $transactionData['transaction']['source_id'])
                ->first()) {
                $transaction= StoreTransaction::run(
                    order:     $order,
                    modelData: $transactionData['transaction']
                );

                DB::connection('aurora')->table('Order Transaction Fact')
                    ->where('Order Transaction Fact Key', $transaction->source_id)
                    ->update(['aiku_id' => $transaction->id]);

                return $transaction;
            }
        }


        return null;
    }
}
