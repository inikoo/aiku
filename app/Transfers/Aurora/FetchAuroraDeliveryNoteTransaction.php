<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemStatusEnum;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeliveryNoteTransaction extends FetchAurora
{
    protected function parseDeliveryNoteTransaction(DeliveryNote $deliveryNote): void
    {


        if ($this->auroraModelData->{'Part SKU'}) {

            $orgStock = $this->parseOrgStock($this->organisation->id.':'.$this->auroraModelData->{'Part SKU'});


            if ($orgStock) {
                $auroraTransaction = $this->organisation->id.':'.$this->auroraModelData->{'Map To Order Transaction Fact Key'};

                $transaction = $this->parseTransaction($auroraTransaction);

                $this->parsedData['type'] = $this->auroraModelData->{'Inventory Transaction Type'};


                $state = match ($deliveryNote->state) {
                    DeliveryNoteStateEnum::SUBMITTED, DeliveryNoteStateEnum::IN_QUEUE, DeliveryNoteStateEnum::PICKER_ASSIGNED => DeliveryNoteItemStateEnum::ON_HOLD,
                    DeliveryNoteStateEnum::PICKING => DeliveryNoteItemStateEnum::PICKING,
                    DeliveryNoteStateEnum::PICKED  => DeliveryNoteItemStateEnum::PICKED,

                    DeliveryNoteStateEnum::PACKING => DeliveryNoteItemStateEnum::PACKING,

                    DeliveryNoteStateEnum::PACKED    => DeliveryNoteItemStateEnum::PACKED,
                    DeliveryNoteStateEnum::FINALISED => DeliveryNoteItemStateEnum::FINALISED,
                    DeliveryNoteStateEnum::SETTLED   => DeliveryNoteItemStateEnum::SETTLED,
                };


                $quantity_required   = $this->auroraModelData->{'Required'};
                $quantity_dispatched = -$this->auroraModelData->{'Inventory Transaction Quantity'};

                $status = $deliveryNote->status;

                if ($status == DeliveryNoteItemStatusEnum::DISPATCHED) {
                    if ($quantity_dispatched < $quantity_required) {
                        $status = DeliveryNoteItemStatusEnum::DISPATCHED_WITH_MISSING;
                    }

                    if ($quantity_dispatched == 0) {
                        $status = DeliveryNoteItemStatusEnum::FAIL;
                    }
                }


                if (!$transaction) {
                    //  print "Warning Transaction ID  ".$this->auroraModelData->{'Map To Order Transaction Fact Key'}." not found while creating DN item >".$this->auroraModelData->{'Inventory Transaction Key'}."\n";
                    $transactionID = null;
                } else {
                    $transactionID = $transaction->id;
                }

                $this->parsedData['delivery_note_item'] = [
                    'transaction_id'      => $transactionID,
                    'state'               => $state,
                    'status'              => $status,
                    'quantity_required'   => $quantity_required,
                    'quantity_picked'     => $this->auroraModelData->{'Picked'},
                    'quantity_packed'     => $this->auroraModelData->{'Packed'},
                    'quantity_dispatched' => $quantity_dispatched,
                    'org_stock_id'        => $orgStock->id,
                    'source_id'           => $this->organisation->id.':'.$this->auroraModelData->{'Inventory Transaction Key'},
                    'created_at'          => $this->auroraModelData->{'Date Created'},

                ];
            } else {
                print "Warning Part SKU  ".$this->auroraModelData->{'Part SKU'}." not found while creating DN item >".$this->auroraModelData->{'Inventory Transaction Key'}."\n";
                dd('xx');

            }
        } else {
            print "Warning Part SKU missing in inventory transaction >".$this->auroraModelData->{'Inventory Transaction Key'}."\n";
        }
    }

    public function fetchDeliveryNoteTransaction(int $id, DeliveryNote $deliveryNote): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseDeliveryNoteTransaction($deliveryNote);
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Inventory Transaction Fact')
            ->where('Inventory Transaction Key', $id)->first();
    }
}
