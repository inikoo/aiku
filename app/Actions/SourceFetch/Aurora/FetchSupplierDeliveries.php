<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Helpers\Address\UpdateHistoricAddressToModel;
use App\Actions\Procurement\SupplierDelivery\StoreSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateSupplierDelivery;
use App\Models\Procurement\SupplierDelivery;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchSupplierDeliveries extends FetchAction
{
    public string $commandSignature = 'fetch:supplier-deliveries {tenants?*} {--s|source_id=}';

    public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?SupplierDelivery
    {
        if ($orderData = $tenantSource->fetchSupplierDelivery($tenantSourceId)) {
            if (!empty($orderData['supplierDelivery']['source_id']) and $order = SupplierDelivery::withTrashed()->where('source_id', $orderData['supplierDelivery']['source_id'])
                    ->first()) {
                $order = UpdateSupplierDelivery::run($order, $orderData['supplierDelivery']);

                $currentDeliveryAddress = $order->getAddress('delivery');

                if ($currentDeliveryAddress and $currentDeliveryAddress->checksum != $orderData['delivery_address']->getChecksum()) {
                    $deliveryAddress = StoreHistoricAddress::run($orderData['delivery_address']);
                    UpdateHistoricAddressToModel::run($order, $currentDeliveryAddress, $deliveryAddress, ['scope' => 'delivery']);
                }


                //  $this->fetchTransactions($tenantSource, $order);
                $this->updateAurora($order);


                return $order;
            } else {
                if ($orderData['parent']) {
                    $order = StoreSupplierDelivery::run($orderData['parent'], $orderData['supplierDelivery'], $orderData['delivery_address']);
                    //  $this->fetchTransactions($tenantSource, $order);
                    $this->updateAurora($order);


                    return $order;
                }
                print "Warning Supplier Delivery $tenantSourceId do not have parent\n";
            }
        } else {
            print "Warning error fetching order $tenantSourceId\n";
        }

        return null;
    }

    /*

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
    */

    public function updateAurora(SupplierDelivery $SupplierDelivery): void
    {
        DB::connection('aurora')->table('Supplier Delivery Dimension')
            ->where('Supplier Delivery Key', $SupplierDelivery->source_id)
            ->update(['aiku_id' => $SupplierDelivery->id]);
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
