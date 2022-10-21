<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 22:59:29 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use Illuminate\Support\Facades\DB;


class FetchAuroraInvoiceTransactionHistoricProduct extends FetchAurora
{

    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Product Key'}) {
            $historicProduct = FetchHistoricProducts::run($this->tenantSource, $this->auroraModelData->{'Product Key'});

            $this->parsedData['transaction'] = [
                'item_type'   => 'HistoricProduct',
                'item_id'     => $historicProduct->id,
                'tax_band_id' => $taxBand->id ?? null,

                'quantity'  => $this->auroraModelData->{'Delivery Note Quantity'},
                'net'       => $this->auroraModelData->{'Order Transaction Amount'},
                'source_id' => $this->auroraModelData->{'Order Transaction Fact Key'},

            ];
        } else {
            print "Warning Product Key missing in transaction >".$this->auroraModelData->{'Order Transaction Fact Key'}."\n";
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }

}
