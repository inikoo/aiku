<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:49:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Actions\OMS\Order\StoreOrder;
use App\Actions\OMS\Order\UpdateOrder;
use App\Enums\OMS\Transaction\TransactionTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\OMS\Order;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrders extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:orders {organisations?*}  {--s|source_id=} {--d|db_suffix=} {--w|with=* : Accepted values: transactions payments} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Order
    {

        if ($orderData = $organisationSource->fetchOrder($organisationSourceId)) {
            if (!empty($orderData['order']['source_id']) and $order = Order::withTrashed()->where('source_id', $orderData['order']['source_id'])
                    ->first()) {
                $order=UpdateOrder::run($order, $orderData['order']);

                $currentBillingAddress = $order->getAddress('billing');

                if ($currentBillingAddress->checksum != $orderData['billing_address']->getChecksum()) {
                    $billingAddress = StoreHistoricAddress::run($orderData['billing_address']);
                    UpdateHistoricAddressToModel::run($order, $currentBillingAddress, $billingAddress, ['scope' => 'billing']);
                }

                $currentDeliveryAddress = $order->getAddress('delivery');
                if ($currentDeliveryAddress->checksum != $orderData['delivery_address']->getChecksum()) {
                    $deliveryAddress = StoreHistoricAddress::run($orderData['delivery_address']);
                    UpdateHistoricAddressToModel::run($order, $currentDeliveryAddress, $deliveryAddress, ['scope' => 'delivery']);
                }

                if (in_array('transactions', $this->with)) {
                    $this->fetchTransactions($organisationSource, $order);
                }
                if (in_array('payments', $this->with)) {
                    $this->fetchPayments($organisationSource, $order);
                }
                $this->updateAurora($order);


                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StoreOrder::make()->asFetch(
                        parent: $orderData['parent'],
                        modelData: $orderData['order'],
                        seedBillingAddress: $orderData['billing_address'],
                        seedDeliveryAddress: $orderData['delivery_address'],
                        hydratorsDelay: $this->hydrateDelay
                    );

                    if (in_array('transactions', $this->with)) {
                        $this->fetchTransactions($organisationSource, $order);
                    }
                    if (in_array('payments', $this->with)) {
                        $this->fetchPayments($organisationSource, $order);
                    }
                    $this->updateAurora($order);


                    return $order;
                }
                print "Warning order $organisationSourceId do not have customer\n";
            }
        } else {
            print "Warning error fetching order $organisationSourceId\n";
        }

        return null;
    }

    private function fetchPayments($organisationSource, Order $order): void
    {
        $paymentsToDelete = $order->payments()->pluck('source_id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Payment Bridge')
                ->select('Payment Key')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            $payment = $this->parsePayment($organisationSource, $auroraData->{'Payment Key'});


            if (!in_array($payment->id, $paymentsToDelete)) {
                $order->payments()->attach(
                    $payment->id,
                    [
                        'amount'=> $payment->amount,
                        'share' => 1
                    ]
                );
            }

            $paymentsToDelete = array_diff($paymentsToDelete, [$auroraData->{'Payment Key'}]);
        }

        $order->payments()->whereIn('id', $paymentsToDelete)->delete();
    }


    public function parsePayment($organisationSource, $source_id): Payment
    {
        $payment = Payment::withTrashed()->where('source_id', $source_id)->first();
        if (!$payment) {
            $payment = FetchAuroraPayments::run($organisationSource, $source_id);
        }
        return $payment;
    }


    private function fetchTransactions($organisationSource, $order): void
    {
        $transactionsToDelete = $order->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Transaction Type', 'Order')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
            FetchTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $order);
        }
        $order->transactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }

    public function updateAurora(Order $order): void
    {
        DB::connection('aurora')->table('Order Dimension')
            ->where('Order Key', $order->source_id)
            ->update(['aiku_id' => $order->id]);
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
        $query->orderBy('Order Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Order Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Order Dimension')->update(['aiku_id' => null]);
    }

}
