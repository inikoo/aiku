<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Jan 2023 20:16:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Delivery\DeliveryNote;
use Illuminate\Support\Facades\DB;


class FetchAuroraDeliveryNoteTransaction extends FetchAurora
{

    protected function parseDeliveryNoteTransaction(DeliveryNote $deliveryNote): void
    {
        if ($this->auroraModelData->{'Part SKU'}) {
            if ($stock = $this->parseStock($this->auroraModelData->{'Part SKU'})) {
                $transaction = $this->parseTransaction($this->auroraModelData->{'Map To Order Transaction Fact Key'});


                $this->parsedData['type'] = $this->auroraModelData->{'Inventory Transaction Type'};


                $state = match ($deliveryNote->state) {
                    'submitted',
                    'in-queue',
                    'picker-assigned' => 'on-hold',
                    'packing' => 'picked',
                    'finalised' => 'packed',
                    default => $deliveryNote->state
                };

                $status = 'in-process';
                if (in_array($deliveryNote->state, [
                    'packed',
                    'finalised',
                    'dispatched',
                    'cancelled'
                ])) {
                    $status = 'done';
                }


                if (!$transaction) {
                    print "Warning Transaction ID  ".$this->auroraModelData->{'Map To Order Transaction Fact Key'}." not found while creating DN item >".$this->auroraModelData->{'Inventory Transaction Key'}."\n";
                    $transactionID = null;
                } else {
                    $transactionID = $transaction->id;
                }


                $this->parsedData['delivery_note_item'] = [
                    'transaction_id' => $transactionID,
                    'state'          => $state,
                    'status'         => $status,
                    'required'       => $this->auroraModelData->{'Required'},
                    'quantity'       => -$this->auroraModelData->{'Inventory Transaction Quantity'},
                    'stock_id'       => $stock->id,
                    'source_id'      => $this->auroraModelData->{'Inventory Transaction Key'},
                    'created_at'     => $this->auroraModelData->{'Date Created'},

                ];
            } else {
                print "Warning Part SKU  ".$this->auroraModelData->{'Part SKU'}." not found while creating DN item >".$this->auroraModelData->{'Inventory Transaction Key'}."\n";
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
