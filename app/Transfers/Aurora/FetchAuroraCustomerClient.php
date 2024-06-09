<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Transfers\Aurora\FetchAurora;
use Illuminate\Support\Facades\DB;

class FetchAuroraCustomerClient extends FetchAurora
{
    protected function parseModel(): void
    {
        if (!$this->auroraModelData->{'Customer Client Customer Key'}) {
            return;
        }

        $this->parsedData['customer'] = $this->parseCustomer(
            $this->organisation->id.':'.$this->auroraModelData->{'Customer Client Customer Key'}
        );

        if ($this->auroraModelData->{'Customer Client Status'} == 'Active') {
            $status         = true;
            $deactivated_at = null;
        } else {
            $status         = false;
            $metadata       = json_decode($this->auroraModelData->{'Customer Client Metadata'} ?? '{}');
            $deactivated_at = $metadata->deactivated_date;
        }


        $this->parsedData['customer_client'] =
            [
                'reference'        => $this->auroraModelData->{'Customer Client Code'},
                'status'           => $status,
                'contact_name'     => $this->auroraModelData->{'Customer Client Main Contact Name'},
                'company_name'     => $this->auroraModelData->{'Customer Client Company Name'},
                'email'            => $this->auroraModelData->{'Customer Client Main Plain Email'},
                'phone'            => $this->auroraModelData->{'Customer Client Main Plain Mobile'},
                'source_id'        => $this->organisation->id.':'.$this->auroraModelData->{'Customer Client Key'},
                'created_at'       => $this->auroraModelData->{'Customer Client Creation Date'},
                'deactivated_at'   => $deactivated_at,
                'address'          => $this->parseAddress(prefix: 'Customer Client Contact', auAddressData: $this->auroraModelData)
            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Client Dimension')
            ->where('Customer Client Key', $id)->first();
    }
}
