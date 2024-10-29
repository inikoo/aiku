<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Models\Helpers\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurchaseOrder extends FetchAurora
{
    protected function parseModel(): void
    {
        if (in_array($this->auroraModelData->{'Purchase Order Parent'}, ['Parcel', 'Container'])) {
            print_r($this->auroraModelData);

            return;
        }

        if ($this->auroraModelData->{'Purchase Order State'} == 'Cancelled' and !$this->auroraModelData->{'Purchase Order Public ID'}) {
            return;
        }


        $orgParent = $this->parseProcurementOrderParent(
            $this->auroraModelData->{'Purchase Order Parent'},
            $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Parent Key'}
        );

        if (!$orgParent) {
            print "Error No parent found ".$this->auroraModelData->{'Purchase Order Parent'}."  ".$this->auroraModelData->{'Purchase Order Parent Key'}." ".$this->auroraModelData->{'Purchase Order Parent Name'}."  \n";

            return;
        }


        $this->parsedData["org_parent"] = $orgParent;


        $state = match ($this->auroraModelData->{'Purchase Order State'}) {
            "InProcess" => PurchaseOrderStateEnum::IN_PROCESS,
            "Submitted" => PurchaseOrderStateEnum::SUBMITTED,
            "Cancelled" => PurchaseOrderStateEnum::CANCELLED,
            default => PurchaseOrderStateEnum::CONFIRMED,
        };


        $deliveryStatus = match ($this->auroraModelData->{'Purchase Order State'}) {
            "Placed", "Costing", "InvoiceChecked" => PurchaseOrderDeliveryStatusEnum::SETTLED,
            "NoReceived" => PurchaseOrderDeliveryStatusEnum::NOT_RECEIVED,
            "Cancelled" => PurchaseOrderDeliveryStatusEnum::CANCELLED,
            "Manufactured", "Inputted" => PurchaseOrderDeliveryStatusEnum::READY_TO_SHIP,
            "Dispatched" => PurchaseOrderDeliveryStatusEnum::DISPATCHED,
            "Confirmed" => PurchaseOrderDeliveryStatusEnum::CONFIRMED,
            "Received" => PurchaseOrderDeliveryStatusEnum::RECEIVED,
            "Checked", "QC_Pass" => PurchaseOrderDeliveryStatusEnum::CHECKED,
            default => PurchaseOrderDeliveryStatusEnum::PROCESSING,
        };


        $cancelled_at = null;
        if ($this->auroraModelData->{'Purchase Order State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Purchase Order Cancelled Date'};
        }


        $org_exchange = $this->auroraModelData->{'Purchase Order Currency Exchange'};

        $grp_exchange = GetHistoricCurrencyExchange::run(
            Currency::find($this->parseCurrencyID($this->auroraModelData->{'Purchase Order Currency Code'})),
            $this->organisation->group->currency,
            Carbon::parse($this->auroraModelData->{'Purchase Order Date'})
        );


        $data = [];

        $this->parsedData["purchase_order"] = [
            'date'         => $this->auroraModelData->{'Purchase Order Date'},
            'submitted_at' => $this->parseDate($this->auroraModelData->{'Purchase Order Submitted Date'}),
            'confirmed_at' => $this->parseDate($this->auroraModelData->{'Purchase Order Confirmed Date'}),
            //'manufactured_at' => $this->parseDate($this->auroraModelData->{'Purchase Order Manufactured Date'}),
            //'received_at'     => $this->parseDate($this->auroraModelData->{'Purchase Order Received Date'}),
            //'checked_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Checked Date'}),
            //'settled_at'      => $this->parseDate($this->auroraModelData->{'Purchase Order Consolidated Date'}),

            'parent_code' => $this->auroraModelData->{'Purchase Order Parent Code'},
            'parent_name' => $this->auroraModelData->{'Purchase Order Parent Name'},

            "reference"       => (string)$this->auroraModelData->{'Purchase Order Public ID'} ?? $this->auroraModelData->{'Purchase Order Key'},
            "state"           => $state,
            "delivery_status" => $deliveryStatus,

            "cost_items"    => $this->auroraModelData->{'Purchase Order Items Net Amount'},
            "cost_shipping" => $this->auroraModelData->{'Purchase Order Shipping Net Amount'},
            "cost_total" => $this->auroraModelData->{'Purchase Order Total Amount'},

            "source_id"       => $this->organisation->id.':'.$this->auroraModelData->{'Purchase Order Key'},
            "org_exchange"    => $org_exchange,
            "grp_exchange"    => $grp_exchange,
            "currency_id"     => $this->parseCurrencyID($this->auroraModelData->{'Purchase Order Currency Code'}),
            "created_at"      => $this->auroraModelData->{'Purchase Order Creation Date'},
            "cancelled_at"    => $cancelled_at,
            "data"            => $data,
            'fetched_at'      => now(),
            'last_fetched_at' => now()
        ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Purchase Order Dimension")
            ->where("Purchase Order Key", $id)
            ->first();
    }


}
