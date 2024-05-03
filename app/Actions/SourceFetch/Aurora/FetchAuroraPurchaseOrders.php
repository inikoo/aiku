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
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrders extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:purchase-orders {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?PurchaseOrder
    {
        if ($purchaseOrderData = $organisationSource->fetchPurchaseOrder($organisationSourceId)) {

            //print_r($purchaseOrderData['purchase_order']);
            if (!empty($purchaseOrderData['purchase_order']['source_id']) and $purchaseOrder = PurchaseOrder::withTrashed()->where('source_id', $purchaseOrderData['purchase_order']['source_id'])->first()) {
                $purchaseOrder = UpdatePurchaseOrder::make()->action(
                    purchaseOrder: $purchaseOrder,
                    modelData: $purchaseOrderData['purchase_order'],
                    strict: false
                );


                $currentDeliveryAddress = $purchaseOrder->getAddress('delivery');

                if ($currentDeliveryAddress and $currentDeliveryAddress->checksum != $purchaseOrderData['delivery_address']->getChecksum()) {
                    $deliveryAddress = StoreHistoricAddress::run($purchaseOrderData['delivery_address']);
                    UpdateHistoricAddressToModel::run($purchaseOrder, $currentDeliveryAddress, $deliveryAddress, ['scope' => 'delivery']);
                }


                //  $this->fetchTransactions($organisationSource, $purchaseOrder);
                $this->updateAurora($purchaseOrder);


                return $purchaseOrder;
            } else {
                if ($purchaseOrderData['org_parent']) {
                    //  try {
                    $purchaseOrder = StorePurchaseOrder::make()->action(
                        organisation: $organisationSource->organisation,
                        orgParent: $purchaseOrderData['org_parent'],
                        modelData: $purchaseOrderData['purchase_order'],
                        strict: false
                    );
                    //  } catch (Exception $e) {
                    //      dd($e);
                    //      $this->recordError($organisationSource, $e, $purchaseOrderData['purchase_order'], 'PurchaseOrder', 'store');
                    //      return null;
                    //  }

                    $this->updateAurora($purchaseOrder);


                    return $purchaseOrder;
                }
                print "Warning purchase order ".$purchaseOrderData['purchase_order']['number']."  Id:$organisationSourceId do not have parent\n";
            }
        } else {
            print "Warning error fetching order $organisationSourceId\n";
        }

        return null;
    }

    /*

    private function fetchTransactions($organisationSource, $purchaseOrder): void
    {
        $transactionsToDelete = $purchaseOrder->transactions()->where('type', TransactionTypeEnum::ORDER)->pluck('source_id', 'id')->all();
        foreach (
            DB::connection('aurora')
                ->table('Order Transaction Fact')
                ->select('Order Transaction Fact Key')
                ->where('Order Transaction Type', 'Order')
                ->where('Order Key', $purchaseOrder->source_id)
                ->get() as $auroraData
        ) {
            $transactionsToDelete = array_diff($transactionsToDelete, [$auroraData->{'Order Transaction Fact Key'}]);
            FetchTransactions::run($organisationSource, $auroraData->{'Order Transaction Fact Key'}, $purchaseOrder);
        }
        $purchaseOrder->transactions()->whereIn('id', array_keys($transactionsToDelete))->delete();
    }
    */

    public function updateAurora(PurchaseOrder $purchaseOrder): void
    {
        $sourceData = explode(':', $purchaseOrder->source_id);

        DB::connection('aurora')->table('Purchase Order Dimension')
            ->where('Purchase Order Key', $sourceData[1])
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
