<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\StoreFixedAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Actions\Procurement\StockDelivery\StoreStockDelivery;
use App\Actions\Procurement\StockDelivery\UpdateStockDelivery;
use App\Models\Procurement\StockDelivery;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraStockDeliveries extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:stock-deliveries {organisations?*} {--s|source_id=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?StockDelivery
    {
        if ($orderData = $organisationSource->fetchStockDelivery($organisationSourceId)) {
            if (!empty($orderData['stockDelivery']['source_id']) and $order = StockDelivery::withTrashed()->where('source_id', $orderData['stockDelivery']['source_id'])
                    ->first()) {
                $order = UpdateStockDelivery::run($order, $orderData['stockDelivery']);

                $currentDeliveryAddress = $order->getAddress('delivery');

                if ($currentDeliveryAddress and $currentDeliveryAddress->checksum != $orderData['delivery_address']->getChecksum()) {
                    $deliveryAddress = StoreFixedAddress::run($orderData['delivery_address']);
                    UpdateHistoricAddressToModel::run($order, $currentDeliveryAddress, $deliveryAddress, ['scope' => 'delivery']);
                }


                //  $this->fetchTransactions($organisationSource, $order);
                $this->updateAurora($order);


                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StoreStockDelivery::run($orderData['parent'], $orderData['stockDelivery'], $orderData['delivery_address']);
                    //  $this->fetchTransactions($organisationSource, $order);
                    $this->updateAurora($order);


                    return $order;
                }
                print "Warning Supplier Delivery $organisationSourceId do not have parent\n";
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

    public function updateAurora(StockDelivery $StockDelivery): void
    {
        DB::connection('aurora')->table('Supplier Delivery Dimension')
            ->where('Supplier Delivery Key', $StockDelivery->source_id)
            ->update(['aiku_id' => $StockDelivery->id]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Supplier Delivery Dimension')
            ->select('Supplier Delivery Key as source_id');
        $query->orderBy('Supplier Delivery Date');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Supplier Delivery Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }
}
