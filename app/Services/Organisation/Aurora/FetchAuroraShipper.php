<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 16:48:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraShipper extends FetchAurora
{
    protected function parseModel(): void
    {
        $code = strtolower($this->auroraModelData->{'Shipper Code'});
        $code = str_replace(' ', '-', $code);

        if ($this->organisation->slug == 'sk') {
            if ($code == 'dpd') {
                $code = 'sk-dpd2';
            }
            if (in_array($code, ['tnt','dhl','kuehnenagel','gls','ups'])) {
                $code = 'sk-'.$code;
            }
        }


        if ($this->organisation->slug == 'es') {

            if (in_array($code, ['gls','tnt','dhl','ups'])) {
                $code = 'es-'.$code;
            }
        }

        if ($this->organisation->slug == 'aroma') {

            if (in_array($code, ['apc','gls','tnt','dhl','ups','dpd','simarco'])) {
                $code = 'aro-'.$code;
            }
        }


        $this->parsedData['shipper'] = [

            'code'         => $code,
            'name'         => $this->auroraModelData->{'Shipper Name'},
            'website'      => $this->auroraModelData->{'Shipper Website'},
            'company_name' => $this->auroraModelData->{'Shipper Fiscal Name'},
            'contact_name' => $this->auroraModelData->{'Shipper Name'},
            'phone'        => $this->auroraModelData->{'Shipper Telephone'},
            'status'       => $this->auroraModelData->{'Shipper Active'} === 'Yes',
            'tracking_url' => $this->auroraModelData->{'Shipper Tracking URL'},
            'source_id'    => $this->organisation->id.':'.$this->auroraModelData->{'Shipper Key'},


        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Shipper Dimension')
            ->where('Shipper Key', $id)->first();
    }
}
