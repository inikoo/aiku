<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemStateEnum;
use App\Enums\Dispatching\DeliveryNoteItem\DeliveryNoteItemStatusEnum;
use App\Models\Dispatching\DeliveryNote;
use App\Models\SupplyChain\Stock;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeliveryNoteItem extends FetchAurora
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
                    DeliveryNoteStateEnum::PICKING, DeliveryNoteStateEnum::PICKED, DeliveryNoteStateEnum::PACKING => DeliveryNoteItemStateEnum::HANDLING,

                    DeliveryNoteStateEnum::PACKED => DeliveryNoteItemStateEnum::PACKED,
                    DeliveryNoteStateEnum::FINALISED => DeliveryNoteItemStateEnum::FINALISED,
                    DeliveryNoteStateEnum::SETTLED => DeliveryNoteItemStateEnum::SETTLED,
                };


                $quantity_required   = $this->auroraModelData->{'Required'};
                $quantity_dispatched = -$this->auroraModelData->{'Inventory Transaction Quantity'};

                $status = match ($deliveryNote->status) {
                    DeliveryNoteStatusEnum::HANDLING => DeliveryNoteItemStatusEnum::HANDLING,
                    DeliveryNoteStatusEnum::DISPATCHED => DeliveryNoteItemStatusEnum::DISPATCHED,
                    DeliveryNoteStatusEnum::DISPATCHED_WITH_MISSING => DeliveryNoteItemStatusEnum::DISPATCHED_WITH_MISSING,
                    DeliveryNoteStatusEnum::FAIL => DeliveryNoteItemStatusEnum::FAIL,
                    DeliveryNoteStatusEnum::CANCELLED => DeliveryNoteItemStatusEnum::CANCELLED,
                };


                if ($status == DeliveryNoteItemStatusEnum::DISPATCHED) {
                    if ($quantity_dispatched < $quantity_required) {
                        $status = DeliveryNoteItemStatusEnum::DISPATCHED_WITH_MISSING;
                    }

                    if ($quantity_dispatched == 0) {
                        $status = DeliveryNoteItemStatusEnum::FAIL;
                    }
                }


                $transactionID = $transaction?->id;


                $stock = Stock::withTrashed()->find($orgStock->stock_id);

                $this->parsedData['delivery_note_item'] = [
                    'transaction_id'      => $transactionID,
                    'state'               => $state,
                    'status'              => $status,
                    'quantity_required'   => $quantity_required,
                    'quantity_picked'     => $this->auroraModelData->{'Picked'},
                    'quantity_packed'     => $this->auroraModelData->{'Packed'},
                    'quantity_dispatched' => $quantity_dispatched,
                    'source_id'           => $this->organisation->id.':'.$this->auroraModelData->{'Inventory Transaction Key'},
                    'created_at'          => $this->auroraModelData->{'Date Created'},
                    'fetched_at'          => now(),
                    'last_fetched_at'     => now(),
                    'org_stock_id'        => $orgStock->id,
                    'org_stock_family_id' => $orgStock->org_stock_family_id,
                    'stock_id'            => $stock->id,
                    'stock_family_id'     => $stock->stock_family_id


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
