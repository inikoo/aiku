<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionDeliveryStatusEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrderTransaction extends FetchAurora
{
    protected function parsePurchaseOrderTransaction(PurchaseOrder $purchaseOrder): void
    {
        if (!in_array($this->auroraModelData->{'Purchase Order Transaction Type'}, ['Parcel', 'Container'])) {
            return;
        }



        if ($purchaseOrder->parent_type == 'OrgPartner') {
            $item = $this->parseOrgStock($this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Transaction Part SKU'});
        } else {
            $item = $this->parseHistoricSupplierProduct($this->organisation->id, $this->auroraModelData->{'Supplier Part Historic Key'});

            if (!$item) {
                $item = $this->parseOrgStock($this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Transaction Part SKU'});
            }

        }



        if (!$item) {

            print "PO Transaction Item not found   ".$this->auroraModelData->{'Purchase Order Transaction Fact Key'}."  \n";


            return;
        }

        $this->parsedData['item'] = $item;

        //enum('Cancelled','NoReceived','InProcess','Submitted','ProblemSupplier','Confirmed','Manufactured','QC_Pass','ReceivedAgent','InDelivery','Inputted','Dispatched','Received','Checked','Placed','InvoiceChecked')
        $state = match ($this->auroraModelData->{'Purchase Order Transaction State'}) {
            'Cancelled' => PurchaseOrderTransactionStateEnum::CANCELLED,
            'NoReceived' => PurchaseOrderTransactionStateEnum::NOT_RECEIVED,
            'InProcess' => PurchaseOrderTransactionStateEnum::IN_PROCESS,
            'Submitted' => PurchaseOrderTransactionStateEnum::SUBMITTED,
            'Placed', 'InvoiceChecked' => PurchaseOrderTransactionStateEnum::SETTLED,
            default => PurchaseOrderTransactionStateEnum::CONFIRMED,
        };

        $deliveryStatus = match ($this->auroraModelData->{'Purchase Order Transaction State'}) {
            'Cancelled' => PurchaseOrderTransactionDeliveryStatusEnum::CANCELLED,
            'NoReceived' => PurchaseOrderTransactionDeliveryStatusEnum::NOT_RECEIVED,
            'InProcess', 'Submitted' => PurchaseOrderTransactionDeliveryStatusEnum::PROCESSING,
            'ProblemSupplier', 'Confirmed', 'Inputted' => PurchaseOrderTransactionDeliveryStatusEnum::CONFIRMED,
            'Manufactured', 'ReceivedAgent' => PurchaseOrderTransactionDeliveryStatusEnum::READY_TO_SHIP,
            'Dispatched', 'InDelivery' => PurchaseOrderTransactionDeliveryStatusEnum::DISPATCHED,
            'Received' => PurchaseOrderTransactionDeliveryStatusEnum::RECEIVED,
            'Checked', 'QC_Pass' => PurchaseOrderTransactionDeliveryStatusEnum::CHECKED,
            'Placed', 'InvoiceChecked' => PurchaseOrderTransactionDeliveryStatusEnum::SETTLED,
            default => null
        };


        if ($state == PurchaseOrderTransactionStateEnum::IN_PROCESS) {
            $quantityOrdered = $this->auroraModelData->{'Purchase Order Ordering Units'};
        } else {
            $quantityOrdered = $this->auroraModelData->{'Purchase Order Submitted Units'};
        }


        $this->parsedData['purchase_order_transaction'] = [
            'quantity_ordered' => $quantityOrdered,
            'state'            => $state,
            'delivery_status'  => $deliveryStatus,
            'source_id'        => $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Transaction Fact Key'},
            'created_at'       => $this->auroraModelData->{'Creation Date'},
            'fetched_at'       => now(),
            'last_fetched_at'  => now(),


        ];
    }

    public function fetchAuroraPurchaseOrderTransaction(int $id, PurchaseOrder $purchaseOrder): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parsePurchaseOrderTransaction($purchaseOrder);
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Purchase Order Transaction Fact')
            ->where('Purchase Order Transaction Fact Key', $id)->first();
    }


}
