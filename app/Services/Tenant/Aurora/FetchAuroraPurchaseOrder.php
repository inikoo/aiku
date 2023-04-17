<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrder extends FetchAurora
{
    protected function parseModel(): void
    {
        if (in_array($this->auroraModelData->{'Purchase Order Parent'}, ['Parcel', 'Container'])) {
            return;
        }

        $parent = match ($this->auroraModelData->{'Purchase Order Parent'}) {
            'Agent' => $this->parseAgent($this->auroraModelData->{'Purchase Order Parent Key'}),
            default => $this->parseSupplier($this->auroraModelData->{'Purchase Order Parent Key'})
        };


        //enum('Cancelled','NoReceived','InProcess','Submitted',
        //'Confirmed','Manufactured','QC_Pass','Inputted','Dispatched','Received',
        //'Checked','Placed','Costing','InvoiceChecked')


        $this->parsedData["parent"] = $parent;


        //print ">>".$this->auroraModelData->{'Purchase Order State'}."\n";
        $state = match ($this->auroraModelData->{'Purchase Order State'}) {
            "Cancelled", "NoReceived", "Placed", "Costing", "InvoiceChecked" => PurchaseOrderStateEnum::SETTLED,
            "InProcess" => PurchaseOrderStateEnum::CREATING,
            "Confirmed" => PurchaseOrderStateEnum::CONFIRMED,
            "Manufactured", "QC_Pass" => PurchaseOrderStateEnum::MANUFACTURED,

            "Inputted", "Dispatched" => PurchaseOrderStateEnum::DISPATCHED,
            "Received"  => PurchaseOrderStateEnum::RECEIVED,
            "Checked"   => PurchaseOrderStateEnum::CHECKED,
            "Submitted" => PurchaseOrderStateEnum::SUBMITTED,
        };

        $status = match ($this->auroraModelData->{'Purchase Order State'}) {
            "Placed", "Costing", "InvoiceChecked" => PurchaseOrderStatusEnum::PLACED,
            "NoReceived" => PurchaseOrderStatusEnum::FAIL,
            "Cancelled"  => PurchaseOrderStatusEnum::CANCELLED,
            default      => PurchaseOrderStatusEnum::PROCESSING,
        };


        $cancelled_at = null;
        if ($this->auroraModelData->{'Purchase Order State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Purchase Order Cancelled Date'};
        }


        $data = [];

        $this->parsedData["purchase_order"] = [
            'date'            => $this->auroraModelData->{'Purchase Order Date'},
            'submitted_at'    => $this->parseDate($this->auroraModelData->{'Purchase Order Submitted Date'}),
            'confirmed_at'    => $this->parseDate($this->auroraModelData->{'Purchase Order Confirmed Date'}),
            'manufactured_at' => $this->parseDate($this->auroraModelData->{'Purchase Order Manufactured Date'}),
            'received_at'     => $this->parseDate($this->auroraModelData->{'Purchase Order Received Date'}),
            'checked_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Checked Date'}),
            'settled_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Consolidated Date'}),


            "number" => $this->auroraModelData->{'Purchase Order Public ID'} ?? $this->auroraModelData->{'Purchase Order Key'},
            "state"  => $state,
            "status" => $status,

            "source_id"    => $this->auroraModelData->{'Purchase Order Key'},
            "exchange"     => $this->auroraModelData->{'Purchase Order Currency Exchange'},
            "created_at"   => $this->auroraModelData->{'Purchase Order Creation Date'},
            "cancelled_at" => $cancelled_at,
            "data"         => $data
        ];

        $deliveryAddressData                  = $this->parseAddress(
            prefix: "Order Delivery",
            auAddressData: $this->auroraModelData,
        );
        $this->parsedData["delivery_address"] = new Address(
            $deliveryAddressData,
        );

        $billingAddressData                  = $this->parseAddress(
            prefix: "Order Invoice",
            auAddressData: $this->auroraModelData,
        );
        $this->parsedData["billing_address"] = new Address($billingAddressData);
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Purchase Order Dimension")
            ->where("Purchase Order Key", $id)
            ->first();
    }
}
