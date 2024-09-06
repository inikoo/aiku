<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrderTransaction extends FetchAurora
{
    protected function parsePurchaseOrderTransaction(PurchaseOrder $purchaseOrder): void
    {

        if(! in_array($this->auroraModelData->{'Purchase Order Transaction Type'}, ['Parcel','Container'])) {
            return;
        }

        $historicSupplierProduct = $this->parseHistoricSupplierProduct($this->organisation->id, $this->auroraModelData->{'Supplier Part Historic Key'});



        if (!$historicSupplierProduct) {
            return;
        }

        $this->parsedData['historic_supplier_product']=$historicSupplierProduct;

        //enum('Cancelled','NoReceived','InProcess','Submitted','ProblemSupplier','Confirmed','Manufactured','QC_Pass','ReceivedAgent','InDelivery','Inputted','Dispatched','Received','Checked','Placed','InvoiceChecked')
        $state = match ($this->auroraModelData->{'Purchase Order Transaction State'}) {
            'Cancelled'  => PurchaseOrderTransactionStateEnum::CANCELLED,
            'NoReceived' => PurchaseOrderTransactionStateEnum::NO_RECEIVED,
            'InProcess'  => PurchaseOrderTransactionStateEnum::CREATING,
            'Submitted'  => PurchaseOrderTransactionStateEnum::SUBMITTED,
            'ProblemSupplier', 'Confirmed' => PurchaseOrderTransactionStateEnum::CONFIRMED,
            'Manufactured' => PurchaseOrderTransactionStateEnum::MANUFACTURED,
            'Dispatched'   => PurchaseOrderTransactionStateEnum::DISPATCHED,
            'QC_Pass', 'ReceivedAgent', 'InDelivery', 'Inputted', 'Received', 'Checked' => PurchaseOrderTransactionStateEnum::PROCESSING,
            'Placed', 'InvoiceChecked' => PurchaseOrderTransactionStateEnum::SETTLED,
            default => null
        };

        $this->parsedData['purchase_order_transaction'] = [
            'quantity_ordered'    => $this->auroraModelData->{'Purchase Order Submitted Units'},
            'state'               => $state,
            'source_id'           => $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Transaction Fact Key'},
            'created_at'          => $this->auroraModelData->{'Creation Date'},
            'fetched_at'          => now(),
            'last_fetched_at'     => now(),


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
