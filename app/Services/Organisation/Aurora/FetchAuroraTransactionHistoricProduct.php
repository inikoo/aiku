<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:52:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceUpserts\Aurora\Single\InsertOrderFromSource;
use App\Actions\SourceUpserts\Aurora\Single\UpsertHistoricProductFromSource;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class FetchAuroraTransactionHistoricProduct extends FetchAurora
{

    protected function parseModel(): void
    {
        $historicProduct = UpsertHistoricProductFromSource::run($this->organisationSource, $this->auroraModelData->{'Product Key'});

        $this->parsedData['transaction'] = [
            'item_type'   => 'HistoricProduct',
            'item_id'     => $historicProduct->id,
            'tax_band_id' => $taxBand->id ?? null,

            'quantity'               => $this->auroraModelData->{'Order Quantity'},
            'discounts'              => $this->auroraModelData->{'Order Transaction Total Discount Amount'},
            'net'                    => $this->auroraModelData->{'Order Transaction Amount'},
            'organisation_source_id' => $this->auroraModelData->{'Order Transaction Fact Key'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Transaction Fact')
            ->where('Order Transaction Fact Key', $id)->first();
    }

}
