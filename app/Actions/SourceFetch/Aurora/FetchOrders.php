<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:49:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Enums\Sales\Transaction\TransactionTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Sales\Order;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchOrders extends FetchAction
{
    public string $commandSignature = 'fetch:orders {tenants?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Order
    {
        if ($orderData = $tenantSource->fetchOrder($tenantSourceId)) {
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


                $this->fetchTransactions($tenantSource, $order);
                $this->fetchPayments($tenantSource, $order);
                $this->updateAurora($order);


                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StoreOrder::run($orderData['parent'], $orderData['order'], $orderData['billing_address'], $orderData['delivery_address']);
                    $this->fetchTransactions($tenantSource, $order);
                    $this->updateAurora($order);
                    $this->fetchPayments($tenantSource, $order);


                    return $order;
                }
                print "Warning order $tenantSourceId do not have customer\n";
            }
        } else {
            print "Warning error fetching order $tenantSourceId\n";
        }

        return null;
    }

    private function fetchPayments($tenantSource, Order $order): void
    {
        $paymentsToDelete = $order->payments()->pluck('source_id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Payment Bridge')
                ->select('Payment Key')
                ->where('Order Key', $order->source_id)
                ->get() as $auroraData
        ) {
            $payment = $this->parsePayment($tenantSource, $auroraData->{'Payment Key'});


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


    public function parsePayment($tenantSource, $source_id): Payment
    {
        $payment = Payment::withTrashed()->where('source_id', $source_id)->first();
        if (!$payment) {
            $payment = FetchPayments::run($tenantSource, $source_id);
        }
        return $payment;
    }


    private function fetchTransactions($tenantSource, $order): void
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
            FetchTransactions::run($tenantSource, $auroraData->{'Order Transaction Fact Key'}, $order);
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
}
