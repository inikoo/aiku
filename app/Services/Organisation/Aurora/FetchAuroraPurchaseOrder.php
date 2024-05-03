<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Actions\SourceFetch\Aurora\FetchAuroraDeletedSuppliers;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraPurchaseOrder extends FetchAurora
{
    protected function parseModel(): void
    {
        if (in_array($this->auroraModelData->{'Purchase Order Parent'}, ['Parcel', 'Container'])) {
            return;
        }


        if($this->auroraModelData->{'Purchase Order State'}=='Cancelled' and !$this->auroraModelData->{'Purchase Order Public ID'}) {
            return;
        }

        //todo deal with inter group supplier products


        if ($this->auroraModelData->{'Purchase Order Parent'} == 'Agent') {
            $agentData = DB::connection("aurora")
                ->table("Agent Dimension")
                ->where("Agent Key", $this->auroraModelData->{'Purchase Order Parent Key'})
                ->first();

            $agentSourceSlug = Str::kebab(strtolower($agentData->{'Agent Code'}));
            $parent          = $this->parseAgent(
                $agentSourceSlug,
                $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Parent Key'}
            );

            $orgParent = OrgAgent::where('organisation_id', $this->organisation->id)
                ->where('agent_id', $parent->id)->first();

        } else {
            $supplierData = DB::connection("aurora")
                ->table("Supplier Dimension")
                ->where("Supplier Key", $this->auroraModelData->{'Purchase Order Parent Key'})
                ->first();

            if ($supplierData) {
                $supplierSourceSlug = Str::kebab(strtolower($supplierData->{'Supplier Code'}));
                $parent             = $this->parseSupplier(
                    $supplierSourceSlug,
                    $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Parent Key'}
                );
            } else {
                $parent = FetchAuroraDeletedSuppliers::run($this->organisationSource, $this->auroraModelData->{'Purchase Order Parent Key'});
            }

            $orgParent = OrgSupplier::where('organisation_id', $this->organisation->id)
                ->where('supplier_id', $parent->id)->first();

        }


        //enum('Cancelled','NoReceived','InProcess','Submitted',
        //'Confirmed','Manufactured','QC_Pass','Inputted','Dispatched','Received',
        //'Checked','Placed','Costing','InvoiceChecked')


        $this->parsedData["org_parent"] = $orgParent;


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


        $org_exchange = $this->auroraModelData->{'Purchase Order Currency Exchange'};

        $group_exchange = GetHistoricCurrencyExchange::run(
            Currency::find($this->parseCurrencyID($this->auroraModelData->{'Purchase Order Currency Code'})),
            $this->organisation->group->currency,
            Carbon::parse($this->auroraModelData->{'Purchase Order Date'})
        );


        $data = [];

        $this->parsedData["purchase_order"] = [
            'date'            => $this->auroraModelData->{'Purchase Order Date'},
            'submitted_at'    => $this->parseDate($this->auroraModelData->{'Purchase Order Submitted Date'}),
            'confirmed_at'    => $this->parseDate($this->auroraModelData->{'Purchase Order Confirmed Date'}),
            'manufactured_at' => $this->parseDate($this->auroraModelData->{'Purchase Order Manufactured Date'}),
            'received_at'     => $this->parseDate($this->auroraModelData->{'Purchase Order Received Date'}),
            'checked_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Checked Date'}),
            'settled_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Consolidated Date'}),


            "number" => (string) $this->auroraModelData->{'Purchase Order Public ID'} ?? $this->auroraModelData->{'Purchase Order Key'},
            "state"  => $state,
            "status" => $status,

            "cost_items"    => $this->auroraModelData->{'Purchase Order Items Net Amount'},
            "cost_shipping" => $this->auroraModelData->{'Purchase Order Shipping Net Amount'},

            "cost_total" => $this->auroraModelData->{'Purchase Order Total Amount'},

            "source_id"      => $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Key'},
            "org_exchange"   => $org_exchange,
            "group_exchange" => $group_exchange,
            "currency_id"    => $this->parseCurrencyID($this->auroraModelData->{'Purchase Order Currency Code'}),
            "created_at"     => $this->auroraModelData->{'Purchase Order Creation Date'},
            "cancelled_at"   => $cancelled_at,
            "data"           => $data
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
