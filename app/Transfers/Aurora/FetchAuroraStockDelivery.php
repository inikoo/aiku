<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Transfers\Aurora\FetchAuroraDeletedSuppliers;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Helpers\Address;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraStockDelivery extends FetchAurora
{
    protected function parseModel(): void
    {

        if ($this->auroraModelData->{'Supplier Delivery Parent'} == 'Agent') {
            $agentData = DB::connection("aurora")
                ->table("Agent Dimension")
                ->where("Agent Key", $this->auroraModelData->{'Supplier Delivery Parent Key'})
                ->first();

            $agentSourceSlug = Str::kebab(strtolower($agentData->{'Agent Code'}));
            $parent          = $this->parseAgent(
                $agentSourceSlug,
                $this->organisation->id.':'.$this->auroraModelData->{'Supplier Delivery Parent Key'}
            );

            $orgParent = OrgAgent::where('organisation_id', $this->organisation->id)
                ->where('agent_id', $parent->id)->first();

        } else {
            $supplierData = DB::connection("aurora")
                ->table("Supplier Dimension")
                ->where("Supplier Key", $this->auroraModelData->{'Supplier Delivery Parent Key'})
                ->first();

            if ($supplierData) {

                if($supplierData->aiku_ignore=='Yes') {
                    return;
                }

                $supplierSourceSlug = Str::kebab(strtolower($supplierData->{'Supplier Code'}));
                $parent             = $this->parseSupplier(
                    $supplierSourceSlug,
                    $this->organisation->id.':'.$this->auroraModelData->{'Supplier Delivery Parent Key'}
                );
            } else {
                $parent = FetchAuroraDeletedSuppliers::run($this->organisationSource, $this->auroraModelData->{'Supplier Delivery Parent Key'});
            }

            $orgParent = OrgSupplier::where('organisation_id', $this->organisation->id)
                ->where('supplier_id', $parent->id)->first();

        }


        $this->parsedData["org_parent"] = $orgParent;




        //print ">>".$this->auroraModelData->{'Supplier Delivery State'}."\n";
        $state = match ($this->auroraModelData->{'Supplier Delivery State'}) {
            "Cancelled", "NoReceived", "Placed", "Costing", "InvoiceChecked" => PurchaseOrderStateEnum::SETTLED,
            "InProcess" => PurchaseOrderStateEnum::CREATING,
            "Confirmed" => PurchaseOrderStateEnum::CONFIRMED,
            "Manufactured", "QC_Pass" => PurchaseOrderStateEnum::MANUFACTURED,

            "Inputted", "Dispatched" => PurchaseOrderStateEnum::DISPATCHED,
            "Received"  => PurchaseOrderStateEnum::RECEIVED,
            "Checked"   => PurchaseOrderStateEnum::CHECKED,
            "Submitted" => PurchaseOrderStateEnum::SUBMITTED,
        };

        $status = match ($this->auroraModelData->{'Supplier Delivery State'}) {
            "Placed", "Costing", "InvoiceChecked" => PurchaseOrderStatusEnum::PLACED,
            "NoReceived" => PurchaseOrderStatusEnum::FAIL,
            "Cancelled"  => PurchaseOrderStatusEnum::CANCELLED,
            default      => PurchaseOrderStatusEnum::PROCESSING,
        };


        $cancelled_at = null;
        if ($this->auroraModelData->{'Supplier Delivery State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Supplier Delivery Cancelled Date'};
        }


        $data = [];

        $this->parsedData["purchase_order"] = [
            'date'            => $this->auroraModelData->{'Supplier Delivery Date'},

            'dispatched_at'      => $this->parseDate($this->auroraModelData->{'Supplier Delivery Submitted Date'}),
            'confirmed_at'       => $this->parseDate($this->auroraModelData->{'Supplier Delivery Confirmed Date'}),
            'placed_at'          => $this->parseDate($this->auroraModelData->{'Supplier Delivery Manufactured Date'}),
            'received_at'        => $this->parseDate($this->auroraModelData->{'Supplier Delivery Received Date'}),
            'cancelled_at'       => $this->parseDate($this->auroraModelData->{'Supplier Delivery Checked Date'}),

            'parent_code'=> $this->auroraModelData->{'Supplier Delivery Parent Code'},
            'parent_name'=> $this->auroraModelData->{'Supplier Delivery Parent Name'},

            "number" => $this->auroraModelData->{'Supplier Delivery Public ID'} ?? $this->auroraModelData->{'Supplier Delivery Key'},
            "state"  => $state,
            "status" => $status,

            "cost_items"    => $this->auroraModelData->{'Supplier Delivery Items Net Amount'},
            "cost_shipping" => $this->auroraModelData->{'Supplier Delivery Shipping Net Amount'},

            "cost_total" => $this->auroraModelData->{'Supplier Delivery Total Amount'},

            "source_id"    => $this->auroraModelData->{'Supplier Delivery Key'},
            "exchange"     => $this->auroraModelData->{'Supplier Delivery Currency Exchange'},
            "currency_id"  => $this->parseCurrencyID($this->auroraModelData->{'Supplier Delivery Currency Code'}),
            "created_at"   => $this->auroraModelData->{'Supplier Delivery Creation Date'},
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
            ->table("Supplier Delivery Dimension")
            ->where("Supplier Delivery Key", $id)
            ->first();
    }
}
