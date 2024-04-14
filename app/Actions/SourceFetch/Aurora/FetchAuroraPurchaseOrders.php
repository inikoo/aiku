<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Models\Procurement\PurchaseOrder;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrders extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:purchase-orders {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?PurchaseOrder
    {
        if ($orderData = $organisationSource->fetchPurchaseOrder($organisationSourceId)) {
            if (!empty($orderData['purchase_order']['source_id']) and $order = PurchaseOrder::withTrashed()->where('source_id', $orderData['purchase_order']['source_id'])
                    ->first()) {
                $order = UpdatePurchaseOrder::run($order, $orderData['purchase_order']);

                $currentDeliveryAddress = $order->getAddress('delivery');

                if ($currentDeliveryAddress and $currentDeliveryAddress->checksum != $orderData['delivery_address']->getChecksum()) {
                    $deliveryAddress = StoreHistoricAddress::run($orderData['delivery_address']);
                    UpdateHistoricAddressToModel::run($order, $currentDeliveryAddress, $deliveryAddress, ['scope' => 'delivery']);
                }


                //  $this->fetchTransactions($organisationSource, $order);
                $this->updateAurora($order);


                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StorePurchaseOrder::run($orderData['parent'], $orderData['purchase_order'], $orderData['delivery_address']);
                    //  $this->fetchTransactions($organisationSource, $order);
                    $this->updateAurora($order);


                    return $order;
                }
                print "Warning purchase order ".$orderData['purchase_order']['number']."  Id:$organisationSourceId do not have parent\n";
            }
        } else {
            print "Warning error fetching order $organisationSourceId\n";
        }

        return null;
    }

    /*

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
    */

    public function updateAurora(PurchaseOrder $purchaseOrder): void
    {
        DB::connection('aurora')->table('Purchase Order Dimension')
            ->where('Purchase Order Key', $purchaseOrder->source_id)
            ->update(['aiku_id' => $purchaseOrder->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Purchase Order Dimension')
            ->select('Purchase Order Key as source_id')
            ->whereIn('Purchase Order Type', ['Parcel', 'Container']);
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        $query->orderBy('Purchase Order Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Purchase Order Dimension')
            ->whereIn('Purchase Order Type', ['Parcel', 'Container']);

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Purchase Order Dimension')->update(['aiku_id' => null]);
    }
}
