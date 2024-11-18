<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:49:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Order\UpdateOrder;
use App\Models\Ordering\Order;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\Aurora\WithAuroraParsers;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraOrders extends FetchAuroraAction
{
    use WithAuroraAttachments;
    use WithAuroraParsers;

    public string $commandSignature = 'fetch:orders {organisations?*} {--S|shop= : Shop slug} {--s|source_id=} {--d|db_suffix=} {--w|with=* : Accepted values: transactions payments full} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    private bool $errorReported = false;

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId, bool $forceWithTransactions = false): ?Order
    {

        $this->organisationSource = $organisationSource;

        if ($orderData = $organisationSource->fetchOrder($organisationSourceId)) {
            $order = $this->processFetchOrder($organisationSource, $orderData);


            if (!$order) {
                if (!$this->errorReported) {
                    $this->recordFetchError($organisationSource, $orderData, 'Order', 'fetching');
                }

                return null;
            }

            if (in_array('transactions', $this->with) or in_array('full', $this->with) or $forceWithTransactions) {
                $this->fetchTransactions($organisationSource, $order);
                $this->fetchNoProductTransactions($organisationSource, $order);
            }
            if (in_array('payments', $this->with) or in_array('full', $this->with)) {
                $this->fetchPayments($organisationSource, $order);
            }

            $sourceData = explode(':', $order->source_id);


            if (in_array('full', $this->with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Delivery Note Dimension')
                        ->where('Delivery Note Order Key', $sourceData[1])
                        ->select('Delivery Note Key as source_id')
                        ->orderBy('source_id')->get() as $deliveryNote
                ) {
                    FetchAuroraDeliveryNotes::run($organisationSource, $deliveryNote->source_id, true);
                }
            }

            if (in_array('full', $this->with)) {
                foreach (
                    DB::connection('aurora')
                        ->table('Invoice Dimension')
                        ->where('Invoice Order Key', $sourceData[1])
                        ->select('Invoice Key as source_id')
                        ->orderBy('source_id')->get() as $invoice
                ) {
                    FetchAuroraInvoices::run($organisationSource, $invoice->source_id, true);
                }


                foreach (
                    DB::connection('aurora')
                        ->table('Order Sent Email Bridge')
                        ->where('Order Sent Email Order Key', $sourceData[1])
                        ->select('Order Sent Email Bridge Key as source_id')
                        ->orderBy('Order Sent Email Bridge Key')->get() as $orderHasDispatchedEmail
                ) {
                    FetchAuroraOrderDispatchedEmails::run($organisationSource, $orderHasDispatchedEmail->source_id, true);
                }


            }


            return $order;
        }

        return null;
    }

    private function processFetchOrder($organisationSource, $orderData): ?Order
    {
        $order = null;
        if (!empty($orderData['order']['source_id']) and $order = Order::withTrashed()->where('source_id', $orderData['order']['source_id'])->first()) {
            try {
                $order = UpdateOrder::make()->action(
                    order: $order,
                    modelData: ['order'],
                    hydratorsDelay: 60,
                    strict: false,
                    audit: false
                );
            } catch (Exception $e) {
                $this->recordError($organisationSource, $e, $orderData['order'], 'Order', 'update');
                $this->errorReported = true;
            }
        } elseif ($orderData['parent']) {
            try {
                $order = StoreOrder::make()->action(
                    parent: $orderData['parent'],
                    modelData: $orderData['order'],
                    strict: false,
                    hydratorsDelay: $this->hydratorsDelay,
                    audit: false
                );

                Order::enableAuditing();
                $this->saveMigrationHistory(
                    $order,
                    Arr::except($orderData['order'], ['fetched_at', 'last_fetched_at', 'source_id'])
                );

                $this->recordNew($organisationSource);

                $sourceData = explode(':', $order->source_id);
                DB::connection('aurora')->table('Order Dimension')
                    ->where('Order Key', $sourceData[1])
                    ->update(['aiku_id' => $order->id]);
            } catch (Exception|Throwable $e) {
                $this->recordError($organisationSource, $e, $orderData['order'], 'Order', 'store');
                $this->errorReported = true;

                return null;
            }
        }

        $this->processFetchAttachments($order, 'Order', $orderData['order']['source_id']);

        return $order;
    }

    private function fetchPayments($organisationSource, Order $order): void
    {
        $organisation = $organisationSource->getOrganisation();

        $paymentsToDelete = $order->payments()->pluck('source_id')->all();
        $sourceData = explode(':', $order->source_id);
        $modelHasPayments = [];
        foreach (

            DB::connection('aurora')
                ->table('Order Payment Bridge')
                ->select('Payment Key')
                ->where('Order Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $payment = $this->parsePayment($organisation->id.':'.$auroraData->{'Payment Key'});

            $modelHasPayments[$payment->id] = [
                'amount' => $payment->amount,
                'share'  => 1
            ];

            $paymentsToDelete = array_diff($paymentsToDelete, [$organisation->id.':'.$auroraData->{'Payment Key'}]);
        }

        $order->payments()->syncWithoutDetaching($modelHasPayments);


        $order->payments()->whereIn('id', $paymentsToDelete)->delete();
    }




    private function fetchTransactions($organisationSource, Order $order): void
    {
        $transactionsToDelete = $order->transactions()->whereIn('model_type', ['Product', 'Service'])->pluck('source_id', 'id')->all();


        $sourceData = explode(':', $order->source_id);
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$organisationSource->getOrganisation()->id.':'.$auroraData->{'Order Transaction Fact Key'}]);
            FetchAuroraTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        }
        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->forceDelete();
    }

    private function fetchNoProductTransactions($organisationSource, Order $order): void
    {
        $transactionsToDelete = $order->transactions()->whereNotIn('model_type', ['Product', 'Service'])->pluck('source_alt_id', 'id')->all();

        $sourceData = explode(':', $order->source_id);
        foreach (
            DB::connection('aurora')
                ->table('Order No Product Transaction Fact')
                ->select('Order No Product Transaction Fact Key')
                ->where('Order Key', $sourceData[1])
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$organisationSource->getOrganisation()->id.':'.$auroraData->{'Order No Product Transaction Fact Key'}]);

            FetchAuroraNoProductTransactions::run($organisationSource, $auroraData->{'Order No Product Transaction Fact Key'}, $order);
        }
        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->forceDelete();
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Order Dimension')
            ->select('Order Key as source_id')
            ->where('Order State', '!=', 'InBasket');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Order Store Key', $sourceData[1]);
        }

        $query->orderBy('Order Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Order Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Order Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Order Dimension')->update(['aiku_id' => null]);
    }

}
