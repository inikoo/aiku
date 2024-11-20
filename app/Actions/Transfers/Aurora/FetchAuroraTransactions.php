<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Discounts\TransactionHasOfferComponent\StoreTransactionHasOfferComponent;
use App\Actions\Discounts\TransactionHasOfferComponent\UpdateTransactionHasOfferComponent;
use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\Ordering\Transaction\UpdateTransaction;
use App\Models\Discounts\TransactionHasOfferComponent;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\Organisation;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchAuroraTransactions
{
    use AsAction;
    use WithAuroraParsers;


    private SourceOrganisationService $organisationSource;

    public function handle(SourceOrganisationService $organisationSource, int $source_id, Order $order): ?Transaction
    {
        $this->organisationSource = $organisationSource;

        $transactionData = $organisationSource->fetchTransaction(id: $source_id);
        if (!$transactionData) {
            return null;
        }

        $transactionData['transaction']['org_exchange']   = GetHistoricCurrencyExchange::run($order->shop->currency, $order->organisation->currency, $transactionData['transaction']['date']);
        $transactionData['transaction']['grp_exchange']   = GetHistoricCurrencyExchange::run($order->shop->currency, $order->group->currency, $transactionData['transaction']['date']);
        $transactionData['transaction']['org_net_amount'] = $transactionData['transaction']['net_amount'] * $transactionData['transaction']['org_exchange'];
        $transactionData['transaction']['grp_net_amount'] = $transactionData['transaction']['net_amount'] * $transactionData['transaction']['grp_exchange'];


        if ($order->submitted_at) {
            $transactionData['transaction']['submitted_at'] = $order->submitted_at;
        }
        if ($order->in_warehouse_at) {
            $transactionData['transaction']['in_warehouse_at'] = $order->in_warehouse_at;
        }

        if ($transaction = Transaction::where('source_id', $transactionData['transaction']['source_id'])->first()) {
            $transaction = UpdateTransaction::make()->action(
                transaction: $transaction,
                modelData: $transactionData['transaction'],
                strict: false
            );
        } else {
            $transaction = StoreTransaction::make()->action(
                order: $order,
                historicAsset: $transactionData['historic_asset'],
                modelData: $transactionData['transaction'],
                strict: false
            );

            $sourceData = explode(':', $transaction->source_id);
            DB::connection('aurora')->table('Order Transaction Fact')
                ->where('Order Transaction Fact Key', $sourceData[1])
                ->update(['aiku_id' => $transaction->id]);
        }

        $this->fetchOfferComponents($organisationSource, $transaction);


        return $transaction;
    }

    private function fetchOfferComponents($organisationSource, Transaction $transaction): void
    {
        $organisation            = $organisationSource->getOrganisation();
        $offerComponentsToDelete = $transaction->offerComponents()->pluck('source_id')->all();
        $sourceData              = explode(':', $transaction->source_id);

        foreach (

            DB::connection('aurora')
                ->table('Order Transaction Deal Bridge')
                ->where('Order Transaction Fact Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionHasOfferComponentData = $this->parseTransactionHasOfferComponentData($transaction, $organisation, $auroraData);
            $offerComponent                   = Arr::pull($transactionHasOfferComponentData, 'offerComponent');
            $transactionHasOfferComponent      = TransactionHasOfferComponent::where('source_id', $transactionHasOfferComponentData['source_id'])->first();

            if ($transactionHasOfferComponent) {
                $transactionHasOfferComponent = UpdateTransactionHasOfferComponent::make()->action(
                    transactionHasOfferComponent: $transactionHasOfferComponent,
                    modelData: $transactionHasOfferComponentData,
                    hydratorsDelay: 60,
                    strict: false
                );
            }

            if (!$transactionHasOfferComponent) {
                $transactionHasOfferComponent = StoreTransactionHasOfferComponent::make()->action(
                    transaction: $transaction,
                    offerComponent: $offerComponent,
                    modelData: $transactionHasOfferComponentData,
                    hydratorsDelay: 60,
                    strict: false
                );
            }

            if ($transactionHasOfferComponent) {
                $offerComponentsToDelete = array_diff($offerComponentsToDelete, [$organisation->id.':'.$auroraData->{'Order Transaction Deal Key'}]);
            }
        }

        $transaction->offerComponents()->whereIn('id', $offerComponentsToDelete)->delete();
    }


    private function parseTransactionHasOfferComponentData(Transaction $transaction, Organisation $organisation, $auroraData): array
    {

        if ($auroraData->{'Deal Component Key'} == '0') {
            $offerComponent = $transaction->shop->offerComponents()->where('is_discretionary', true)->first();
        } else {
            $offerComponent = $this->parseOfferComponent($organisation->id.':'.$auroraData->{'Deal Component Key'});
        }

        if (!$offerComponent) {
            print 'No offer component found for '.$auroraData->{'Deal Component Key'}."\n";
            dd($auroraData);
        }

        $data = [
            'source_id'             => $organisation->id.':'.$auroraData->{'Order Transaction Deal Key'},
            'offer_component_id'     => $offerComponent->id,
            'discounted_amount'     => $auroraData->{'Amount Discount'},
            'discounted_percentage' => $auroraData->{'Fraction Discount'},
            'info'                  => $auroraData->{'Deal Info'},
            'is_pinned'             => $auroraData->{'Order Transaction Deal Pinned'} == 'Yes',
            'offerComponent'        => $offerComponent,
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
        ];

        if (!($auroraData->{'Order Transaction Deal Metadata'} == '' or $auroraData->{'Order Transaction Deal Metadata'} == '{}')) {
            $data['data'] = json_decode($auroraData->{'Order Transaction Deal Metadata'}, true);
        }


        return $data;
    }

}
