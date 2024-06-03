<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 22:59:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

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

            $this->parsedData['transaction'] = [
                'order_id'    => $order->id,
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
