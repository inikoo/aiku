<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:29:05 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraSupplier extends FetchAurora
{

    protected function parseModel(): void
    {
        $deleted_at = $this->parseDate($this->auroraModelData->{'Supplier Valid To'});
        if ($this->auroraModelData->{'Supplier Type'} != 'Archived') {
            $deleted_at = null;
        }
        $phone = $this->auroraModelData->{'Supplier Main Plain Mobile'};
        if ($phone == '') {
            $phone = $this->auroraModelData->{'Supplier Main Plain Telephone'};
        }

        $this->parsedData['supplier'] =
            [
                'type'         => 'supplier',
                'name'         => $this->auroraModelData->{'Supplier Name'},
                'code'         => preg_replace('/\s/', '-', $this->auroraModelData->{'Supplier Code'}),
                'company_name' => $this->auroraModelData->{'Supplier Company Name'},
                'contact_name' => $this->auroraModelData->{'Supplier Main Contact Name'},
                'email'        => $this->auroraModelData->{'Supplier Main Plain Email'},
                'phone'        => $phone,
                'currency_id'  => $this->parseCurrencyID($this->auroraModelData->{'Supplier Default Currency Code'}),
                'source_id'    => $this->auroraModelData->{'Supplier Key'},
                'created_at'   => $this->parseDate($this->auroraModelData->{'Supplier Valid From'}),
                'deleted_at'   => $deleted_at,

            ];
        $this->parsedData['address']  = $this->parseAddress(prefix: 'Supplier Contact', auAddressData: $this->auroraModelData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Supplier Dimension')
            ->where('Supplier Key', $id)->first();
    }

}
