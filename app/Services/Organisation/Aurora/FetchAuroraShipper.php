<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 16:48:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraShipper extends FetchAurora
{

    protected function parseModel(): void
    {
        $this->parsedData['shipper'] = [

            'code'         => Str::snake(strtolower($this->auroraModelData->{'Shipper Code'}), '-'),
            'name'         => $this->auroraModelData->{'Shipper Name'},
            'website'      => $this->auroraModelData->{'Shipper Website'},
            'company_name' => $this->auroraModelData->{'Shipper Fiscal Name'},
            'contact_name' => $this->auroraModelData->{'Shipper Name'},
            'phone'        => $this->auroraModelData->{'Shipper Telephone'},
            'status'       => $this->auroraModelData->{'Shipper Active'} === 'Yes',
            'tracking_url' => $this->auroraModelData->{'Shipper Tracking URL'},

        ];
        $this->parsedData['source']  = [
            'organisation_source_id' => $this->auroraModelData->{'Shipper Key'},

        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Shipper Dimension')
            ->where('Shipper Key', $id)->first();
    }

}
