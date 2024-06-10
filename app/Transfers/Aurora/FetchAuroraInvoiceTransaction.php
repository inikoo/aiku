<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraInvoiceTransaction extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Key'}) {
            $historicItem = $this->parseTransactionItem(
                $this->organisation,
                $this->auroraModelData->{'Product Key'}
            );

            $this->parsedData['historic_asset'] = $historicItem;

            $order=$this->parseOrder($this->organisation->id.':'.$this->auroraModelData->{'Order Key'});

            if(!$order) {
                print "Order not found >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
            }

            $this->parsedData['transaction'] = [
                'order_id'    => $order?->id,
                'tax_band_id' => $taxBand->id ?? null,
                'quantity'    => $this->auroraModelData->{'Delivery Note Quantity'},
                'net_amount'  => $this->auroraModelData->{'Order Transaction Amount'},
                'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Order Transaction Fact Key'},

            ];


        } else {
            print "Warning Asset Key missing in transaction >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }
}
