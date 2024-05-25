<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 17:12:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceFetch\Aurora\FetchAuroraCustomerClients;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrder extends FetchAurora
{
    protected function parseModel(): void
    {




        $deliveryData = [];

        if ($this->auroraModelData->{'Order For Collection'} == 'Yes') {
            $deliveryData['collection'] = true;
        } else {
            if ($this->auroraModelData->{'Order Email'}) {
                $deliveryData['email'] = $this->auroraModelData->{'Order Email'};
            }
            if ($this->auroraModelData->{'Order Telephone'}) {
                $deliveryData['phone'] = $this->auroraModelData->{'Order Telephone'};
            }
        }

        if ($this->auroraModelData->{'Order State'} == "InBasket") {
            return;
        }

        if ($this->auroraModelData->{'Order Customer Client Key'} != "") {
            $parent = FetchAuroraCustomerClients::run(
                $this->organisationSource,
                $this->auroraModelData->{'Order Customer Client Key'},
            );
        } else {
            $parent = $this->parseCustomer(
                $this->organisation->id.':'.$this->auroraModelData->{'Order Customer Key'}
            );
        }

        if($parent->deleted_at and $this->auroraModelData->{'Order State'} == "Cancelled") {
            return;
        }

        $this->parsedData["parent"] = $parent;
        if (!$parent) {
            return;
        }

        $state = match ($this->auroraModelData->{'Order State'}) {
            "InWarehouse", "Packed" => OrderStateEnum::HANDLING,
            "PackedDone" => OrderStateEnum::PACKED,
            "Approved"   => OrderStateEnum::FINALISED,
            "Dispatched" => OrderStateEnum::SETTLED,
            default      => OrderStateEnum::SUBMITTED,
        };


        $status = match ($this->auroraModelData->{'Order State'}) {
            "Cancelled"  => OrderStatusEnum::CANCELLED,
            "Dispatched" => OrderStatusEnum::DISPATCHED,
            default      => OrderStatusEnum::PROCESSING,
        };


        $data = [
            "delivery_data" => $deliveryData
        ];

        $cancelled_at = null;
        if ($this->auroraModelData->{'Order State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Order Cancelled Date'};
            if (!$cancelled_at) {
                $cancelled_at = $this->auroraModelData->{'Order Date'};
            }

            if (
                $this->auroraModelData->{'Order Invoiced Date'}   != "" or
                $this->auroraModelData->{'Order Dispatched Date'} != ""
            ) {
                $stateWhenCancelled = OrderStateEnum::FINALISED;
            } elseif (
                $this->auroraModelData->{'Order Packed Date'}      != "" or
                $this->auroraModelData->{'Order Packed Done Date'} != ""
            ) {
                $stateWhenCancelled = OrderStateEnum::PACKED;
            } elseif (
                $this->auroraModelData->{'Order Send to Warehouse Date'} != ""
            ) {
                $stateWhenCancelled = OrderStateEnum::HANDLING;
            } else {
                $stateWhenCancelled = OrderStateEnum::SUBMITTED;
            }


            $data['cancelled'] = [
                'state' => $stateWhenCancelled
            ];
        }

        $handingType = OrderHandingTypeEnum::SHIPPING;
        if ($this->auroraModelData->{'Order For Collection'} == 'Yes') {
            $handingType = OrderHandingTypeEnum::COLLECTION;
        }

        $deliveryLocked = false;
        $billingLocked  = false;
        if (in_array($this->auroraModelData->{'Order State'}, ['Cancelled', 'Approved', 'Dispatched'])) {
            $deliveryLocked = true;
            $billingLocked  = true;
        }


        $this->parsedData["order"] = [
            'date'            => $this->auroraModelData->{'Order Date'},
            'submitted_at'    => $this->parseDate($this->auroraModelData->{'Order Submitted by Customer Date'}),
            'in_warehouse_at' => $this->parseDate($this->auroraModelData->{'Order Send to Warehouse Date'}),
            'packed_at'       => $this->parseDate($this->auroraModelData->{'Order Packed Date'}),
            'finalised_at'    => $this->parseDate($this->auroraModelData->{'Order Packed Done Date'}),
            'dispatched_at'   => $this->parseDate($this->auroraModelData->{'Order Dispatched Date'}),
            'handing_type'    => $handingType,
            'billing_locked'  => $billingLocked,
            'delivery_locked' => $deliveryLocked,

            "number"          => $this->auroraModelData->{'Order Public ID'},
            'customer_number' => (string)$this->auroraModelData->{'Order Customer Purchase Order ID'},
            "state"           => $state,
            "status"          => $status,
            "source_id"       => $this->organisation->id.':'.$this->auroraModelData->{'Order Key'},

            "created_at"   => $this->auroraModelData->{'Order Created Date'},
            "cancelled_at" => $cancelled_at,
            "data"         => $data
        ];

        $billingAddressData                           = $this->parseAddress(
            prefix: "Order Invoice",
            auAddressData: $this->auroraModelData,
        );
        $this->parsedData['order']["billing_address"] = new Address($billingAddressData);

        if ($handingType == OrderHandingTypeEnum::SHIPPING) {
            $deliveryAddressData                           = $this->parseAddress(
                prefix: "Order Delivery",
                auAddressData: $this->auroraModelData,
            );
            $this->parsedData['order']["delivery_address"] = new Address(
                $deliveryAddressData,
            );
        }
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Order Dimension")
            ->where("Order Key", $id)
            ->first();
    }
}
