<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Feb 2023 21:12:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeliveryNote extends FetchAurora
{
    protected function parseModel(): void
    {


        if(!$this->auroraModelData->{'Delivery Note Order Key'}){
            print "Warning delivery without order";
            return;
        }

        $this->parsedData["order"] = $this->parseOrder($this->auroraModelData->{'Delivery Note Order Key'});

        if(! $this->parsedData["order"]){
            print "Warning delivery without invalid order key (not found) ".$this->auroraModelData->{'Delivery Note Order Key'}."\n";
            return;
        }


        /*
        'submitted',
                    'picker-assigned',
                    'picking',
                    'picked',
                    'packing',
                    'packed',
                    'finalised',
                    'dispatched',*/
        //enum('Ready to be Picked','Picker Assigned','Picking','Picked','Packing','Packed','Packed Done','Approved','Dispatched','Cancelled','Cancelled to Restock')
        $state = match ($this->auroraModelData->{'Delivery Note State'}) {
            'Ready to be Picked' => 'submitted',
            'Picker Assigned' => 'in-queue',
            'Picking' => 'picking',
            'Picked' => 'picked',
            'Packing' => 'packing',
            'Packed' => 'packed',
            'Packed Done', 'Approved' => 'finalised',
            'Dispatched' => 'dispatched',
            'Cancelled', 'Cancelled to Restock' => "cancelled",
            default => "submitted",
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
                'source_id'  => $this->auroraModelData->{'Delivery Note Key'},
            ];
        }


        $this->parsedData['shipment'] = $shipment;

        $this->parsedData["delivery_note"] = [
            "number"       => $this->auroraModelData->{'Delivery Note ID'},
            'date'         => $this->auroraModelData->{'Delivery Note Date Created'},
            "state"        => $state,
            "source_id"    => $this->auroraModelData->{'Delivery Note Key'},
            "created_at"   => $this->auroraModelData->{'Delivery Note Date Created'},
            'picking_at'   => $this->auroraModelData->{'Delivery Note Date Start Picking'},
            'picked_at'    => $this->auroraModelData->{'Delivery Note Date Finish Picking'},
            'packing_at'   => $this->auroraModelData->{'Delivery Note Date Start Packing'},
            'packed_at'    => $this->auroraModelData->{'Delivery Note Date Finish Packing'},
            "cancelled_at" => $cancelled_at,

        ];

        $deliveryAddressData                  = $this->parseAddress(
            prefix:        "Delivery Note",
            auAddressData: $this->auroraModelData,
        );
        $this->parsedData["delivery_address"] = new Address(
            $deliveryAddressData,
        );
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Delivery Note Dimension")
            ->where("Delivery Note Key", $id)
            ->first();
    }
}
