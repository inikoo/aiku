<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 08:42:26 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraAgent extends FetchAurora
{

    protected function parseModel(): void
    {
        $phone = $this->auroraModelData->{'Agent Main Plain Mobile'};
        if ($phone == '') {
            $phone = $this->auroraModelData->{'Agent Main Plain Telephone'};
        }


        $this->parsedData['agent'] =
            [
                'name'         => $this->auroraModelData->{'Agent Name'},
                'code'         => preg_replace('/\s/', '-', $this->auroraModelData->{'Agent Code'}),
                'company_name' => $this->auroraModelData->{'Agent Company Name'},
                'contact_name' => $this->auroraModelData->{'Agent Main Contact Name'},
                'email'        => $this->auroraModelData->{'Agent Main Plain Email'},
                'phone'        => $phone,
                'currency_id'  => $this->parseCurrencyID($this->auroraModelData->{'Agent Default Currency Code'}),
                'source_id'    => $this->auroraModelData->{'Agent Key'},
                'created_at'   => $this->auroraModelData->{'Agent Valid From'}

            ];


        $this->parsedData['address'] = $this->parseAddress(prefix: 'Agent Contact', auAddressData: $this->auroraModelData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Agent Dimension')
            ->where('Agent Key', $id)->first();
    }

}
