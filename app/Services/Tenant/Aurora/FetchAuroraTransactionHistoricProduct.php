<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:52:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchHistoricProducts;
use Illuminate\Support\Facades\DB;


class FetchAuroraTransactionHistoricProduct extends FetchAurora
{

    protected function parseModel(): void
    {
        $historicProduct = FetchHistoricProducts::run($this->tenantSource, $this->auroraModelData->{'Product Key'});

        $this->parsedData['transaction'] = [
            'item_type'   => 'HistoricProduct',
            'item_id'     => $historicProduct->id,
            'tax_band_id' => $taxBand->id ?? null,

            'quantity'               => $this->auroraModelData->{'Order Quantity'},
            'discounts'              => $this->auroraModelData->{'Order Transaction Total Discount Amount'},
            'net'                    => $this->auroraModelData->{'Order Transaction Amount'},
            'source_id' => $this->auroraModelData->{'Order Transaction Fact Key'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }

}
