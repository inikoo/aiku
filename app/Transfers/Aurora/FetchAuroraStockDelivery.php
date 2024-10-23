<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Models\Helpers\Currency;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Support\Facades\DB;

class FetchAuroraStockDelivery extends FetchAurora
{
    protected function parseModel(): void
    {

        if ($this->auroraModelData->{'Supplier Delivery Parent'} == 'Order') {
            return;
        }

        if ($this->auroraModelData->{'Supplier Delivery Parent'} == 'Agent') {




            $agentData = DB::connection("aurora")
                ->table("Agent Dimension")
                ->where("Agent Key", $this->auroraModelData->{'Supplier Delivery Parent Key'})
                ->first();

            $parent          = $this->parseAgent(
                $this->organisation->id.':'.$this->auroraModelData->{'Supplier Delivery Parent Key'}
            );

            if (!$parent) {
                dd($this->auroraModelData);
                return;
            }

            $orgParent = OrgAgent::where('organisation_id', $this->organisation->id)
                ->where('agent_id', $parent->id)->first();

            if (!$orgParent) {
                print "Warning agent ".$parent->id." not found\n";
                return;
            }
        } else {


            $parent = $this->parseSupplier($this->organisation->id.':'.$this->auroraModelData->{'Supplier Delivery Parent Key'});


            if (!$parent) {
                print "Warning supplier ".$this->auroraModelData->{'Supplier Delivery Parent Key'}." not found\n";
                return;
            }

            $orgParent = OrgSupplier::where('organisation_id', $this->organisation->id)->where('supplier_id', $parent->id)->first();

            if (!$orgParent) {
                dd($this->auroraModelData);
                return;
            }
        }


        $this->parsedData["org_parent"] = $orgParent;


        //print ">>".$this->auroraModelData->{'Supplier Delivery State'}."\n";
        $state = match ($this->auroraModelData->{'Supplier Delivery State'}) {
            "Cancelled", "NoReceived", "Placed", "Costing", "InvoiceChecked" => StockDeliveryStateEnum::SETTLED,
            "InProcess", "Confirmed", "Manufactured", "QC_Pass"."Submitted" => StockDeliveryStateEnum::IN_PROCESS,

            "Inputted", "Dispatched" => StockDeliveryStateEnum::DISPATCHED,
            "Received" => StockDeliveryStateEnum::RECEIVED,
            "Checked" => StockDeliveryStateEnum::CHECKED,
        };

        $status = match ($this->auroraModelData->{'Supplier Delivery State'}) {
            "Placed", "Costing", "InvoiceChecked" => StockDeliveryStatusEnum::PLACED,
            "NoReceived" => StockDeliveryStatusEnum::NOT_RECEIVED,
            "Cancelled" => StockDeliveryStatusEnum::CANCELLED,
            default => StockDeliveryStatusEnum::PROCESSING,
        };


        $cancelled_at = null;
        if ($this->auroraModelData->{'Supplier Delivery State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Supplier Delivery Cancelled Date'};
        }




        $data = [];


        $date = $this->parseDatetime($this->auroraModelData->{'Supplier Delivery Last Updated Date'});

        $currencyID  = $this->parseCurrencyID($this->auroraModelData->{'Supplier Delivery Currency Code'});
        $currency    = Currency::find($currencyID);
        $orgExchange = GetHistoricCurrencyExchange::run($currency, $orgParent->organisation->currency, $date);
        $grpExchange = GetHistoricCurrencyExchange::run($currency, $orgParent->group->currency, $date);


        $this->parsedData["stockDelivery"] = [
            'date' => $date,

            'dispatched_at' => $this->parseDate($this->auroraModelData->{'Supplier Delivery Dispatched Date'}),
            'received_at'   => $this->parseDate($this->auroraModelData->{'Supplier Delivery Received Date'}),
            'checked_at'    => $this->parseDate($this->auroraModelData->{'Supplier Delivery Checked Date'}),
            'settled_at'    => $this->parseDate($this->auroraModelData->{'Supplier Delivery Placed Date'}),
            'cancelled_at'  => $cancelled_at,

            'parent_code' => $this->auroraModelData->{'Supplier Delivery Parent Code'},
            'parent_name' => $this->auroraModelData->{'Supplier Delivery Parent Name'},

            "reference" => $this->auroraModelData->{'Supplier Delivery Public ID'} ?? $this->auroraModelData->{'Supplier Delivery Key'},
            "state"     => $state,
            "status"    => $status,

            "cost_items" => $this->auroraModelData->{'Supplier Delivery Items Amount'},
            // "cost_shipping" => $this->auroraModelData->{'Supplier Delivery Shipping Net Amount'},

            //  "cost_total" => $this->auroraModelData->{'Supplier Delivery Total Amount'},

            "source_id" => $this->organisation->id.':'.$this->auroraModelData->{'Supplier Delivery Key'},

            "currency_id"  => $currencyID,
            'org_exchange' => $orgExchange,
            'grp_exchange' => $grpExchange,

            "created_at"      => $this->auroraModelData->{'Supplier Delivery Creation Date'},
            "data"            => $data,
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
        ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Supplier Delivery Dimension")
            ->where("Supplier Delivery Key", $id)
            ->first();
    }
}
