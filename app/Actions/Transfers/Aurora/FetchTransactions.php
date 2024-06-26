<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTransactions
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id, Order $order): ?Transaction
    {
        if ($transactionData = $organisationSource->fetchTransaction(id: $source_id)) {

            if (!Transaction::where('source_id', $transactionData['transaction']['source_id'])->first()) {


                $transactionData['transaction']['org_exchange']=
                    GetHistoricCurrencyExchange::run($order->shop->currency, $order->organisation->currency, $transactionData['transaction']['date']);
                $transactionData['transaction']['group_exchange']=
                    GetHistoricCurrencyExchange::run($order->shop->currency, $order->group->currency, $transactionData['transaction']['date']);
                $transactionData['transaction']['org_net_amount']  = $transactionData['transaction']['net']*$transactionData['transaction']['org_exchange'];
                $transactionData['transaction']['group_net_amount']= $transactionData['transaction']['net']*$transactionData['transaction']['group_exchange'];



                $transaction= StoreTransaction::make()->action(
                    order:     $order,
                    item:     $transactionData['item'],
                    modelData: $transactionData['transaction']
                );

                $sourceData=explode(':', $transaction->source_id);
                DB::connection('aurora')->table('Order Transaction Fact')
                    ->where('Order Transaction Fact Key', $sourceData[1])
                    ->update(['aiku_id' => $transaction->id]);

                return $transaction;
            }
        }


        return null;
    }
}
