<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:08:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraProspect extends FetchAurora
{
    protected function parseModel(): void
    {
        $state = Str::kebab($this->auroraModelData->{'Prospect Status'});

        $customer_id = null;
        if ($this->auroraModelData->{'Prospect Customer Key'}) {
            $customer_id = $this->parseCustomer($this->auroraModelData->{'Prospect Customer Key'})->id;
        }

        $this->parsedData['prospect'] =
            [
                'state'        => $state,
                'contact_name' => $this->auroraModelData->{'Prospect Main Contact Name'},
                'company_name' => $this->auroraModelData->{'Prospect Company Name'},
                'email'        => $this->auroraModelData->{'Prospect Main Plain Email'},
                'phone'        => $this->auroraModelData->{'Prospect Main Plain Mobile'},
                'website'      => $this->auroraModelData->{'Prospect Website'},
                'source_id'    => $this->auroraModelData->{'Prospect Key'},
                'created_at'   => $this->auroraModelData->{'Prospect First Contacted Date'},
                'customer_id'  => $customer_id
            ];

        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Prospect Store Key'});


        $this->parsedData['contact_address'] = $this->parseAddress(prefix: 'Prospect Contact', auAddressData: $this->auroraModelData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Prospect Dimension')
            ->where('Prospect Key', $id)->first();
    }
}
