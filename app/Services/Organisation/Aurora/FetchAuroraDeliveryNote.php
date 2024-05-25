<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Feb 2023 21:12:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeliveryNote extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!$this->auroraModelData->{'Delivery Note Order Key'}) {
            print "Warning delivery without order ".$this->auroraModelData->{'Delivery Note Key'}."  \n";

            return;
        }

        $order     = $this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Delivery Note Order Key'});
        $warehouse = $this->parseWarehouse($this->organisation->id.':'.$this->auroraModelData->{'Delivery Note Warehouse Key'});


        if (!$order) {
            print "Delivery without invalid order key (not found) ".$this->auroraModelData->{'Delivery Note Order Key'}." - ".$this->auroraModelData->{'Delivery Note Key'}."  \n";

            return;
        }


        $this->parsedData["order"] = $order;

        $state = match ($this->auroraModelData->{'Delivery Note State'}) {
            'Picker Assigned' => DeliveryNoteStateEnum::IN_QUEUE,
            'Picking'         => DeliveryNoteStateEnum::PICKING,
            'Picked'          => DeliveryNoteStateEnum::PICKED,
            'Packing'         => DeliveryNoteStateEnum::PACKING,
            'Packed'          => DeliveryNoteStateEnum::PACKED,
            'Packed Done', 'Approved' => DeliveryNoteStateEnum::FINALISED,
            'Dispatched', 'Cancelled', 'Cancelled to Restock' => DeliveryNoteStateEnum::SETTLED,
            default => DeliveryNoteStateEnum::SUBMITTED,
        };

        $status = match ($this->auroraModelData->{'Delivery Note State'}) {
            'Dispatched' => DeliveryNoteStatusEnum::DISPATCHED,
            'Cancelled', 'Cancelled to Restock' => DeliveryNoteStatusEnum::CANCELLED,
            default => DeliveryNoteStatusEnum::HANDLING,
        };

        $cancelled_at = null;
        if ($this->auroraModelData->{'Delivery Note State'} == "Cancelled") {
            $cancelled_at = $this->auroraModelData->{'Delivery Note Date Cancelled'};
            if (!$cancelled_at) {
                $cancelled_at = $this->auroraModelData->{'Delivery Note Date'};
            }
        }


        /*
        $date = match ($state) {
            'packed' => $this->auroraModelData->{'Delivery Note Date Finish Packing'},
            default => $this->auroraModelData->{'Delivery Note Date Created'}
        };
        */

        $shipment  = null;
        $shipperID = null;
        if ($this->auroraModelData->{'Delivery Note Shipper Key'} and $fetchedShipment = $this->parseShipper($this->auroraModelData->{'Delivery Note Shipper Key'})) {
            $shipperID = $fetchedShipment->id;
        }


        if ($state == 'dispatched') {
            $shipmentCode = $this->auroraModelData->{'Delivery Note ID'};
            if ($this->auroraModelData->{'Delivery Note Shipper Consignment'}) {
                $shipmentCode = $this->auroraModelData->{'Delivery Note Shipper Consignment'};
            }

            $shipment = [
                'code'       => $shipmentCode,
                'tracking'   => $this->auroraModelData->{'Delivery Note Shipper Tracking'},
                'shipper_id' => $shipperID,
                "created_at" => $this->auroraModelData->{'Delivery Note Date Dispatched'},
                'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Delivery Note Key'},
            ];
        }

        $weight = $this->auroraModelData->{'Delivery Note Weight'};

        $this->parsedData['shipment'] = $shipment;

        $deliveryAddressData = $this->parseAddress(
            prefix: "Delivery Note",
            auAddressData: $this->auroraModelData,
        );
        $deliveryAddress     = new Address(
            $deliveryAddressData,
        );

        $this->parsedData["delivery_note"] = [
            "number"           => $this->auroraModelData->{'Delivery Note ID'},
            'date'             => $this->auroraModelData->{'Delivery Note Date Created'},
            "state"            => $state,
            "status"           => $status,
            "source_id"        => $this->organisation->id.':'.$this->auroraModelData->{'Delivery Note Key'},
            "created_at"       => $this->auroraModelData->{'Delivery Note Date Created'},
            'picking_at'       => $this->auroraModelData->{'Delivery Note Date Start Picking'},
            'picked_at'        => $this->auroraModelData->{'Delivery Note Date Finish Picking'},
            'packing_at'       => $this->auroraModelData->{'Delivery Note Date Start Packing'},
            'packed_at'        => $this->auroraModelData->{'Delivery Note Date Finish Packing'},
            'finalised_at'     => $this->auroraModelData->{'Delivery Note Date Done Approved'},
            'dispatched_at'    => $this->auroraModelData->{'Delivery Note Date Dispatched'},
            'weight'           => $weight,
            'email'            => $this->auroraModelData->{'Delivery Note Email'},
            'phone'            => $this->auroraModelData->{'Delivery Note Telephone'},
            'delivery_address' => $deliveryAddress,
            'warehouse_id'     => $warehouse->id,
        ];

        if ($cancelled_at) {
            $this->parsedData["delivery_note"]['cancelled_at'] = $cancelled_at;
        }
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Delivery Note Dimension")
            ->where("Delivery Note Key", $id)
            ->first();
    }
}
