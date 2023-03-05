<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:15:57 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class FetchAuroraInvoice extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['order'] = $this->parseOrder($this->auroraModelData->{'Invoice Order Key'});

        $data = [];

        $data['foot_note'] = $this->auroraModelData->{'Invoice Message'};


        $this->parsedData['invoice'] = [
            'number'     => $this->auroraModelData->{'Invoice Public ID'},
            'type'       => strtolower($this->auroraModelData->{'Invoice Type'}),
            'created_at' => $this->auroraModelData->{'Invoice Date'},
            'exchange'   => $this->auroraModelData->{'Invoice Currency Exchange'},
            'net'        => $this->auroraModelData->{'Invoice Total Net Amount'},
            'total'      => $this->auroraModelData->{'Invoice Total Amount'},

            'source_id' => $this->auroraModelData->{'Invoice Key'},
            'data'      => $data

        ];



        $billingAddressData                  = $this->parseAddress(prefix: 'Invoice', auAddressData: $this->auroraModelData);
        $this->parsedData['billing_address'] = new Address($billingAddressData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Invoice Dimension')
            ->where('Invoice Key', $id)->first();
    }
}
