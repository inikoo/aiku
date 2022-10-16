<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 18:07:21 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraWebsite extends FetchAurora
{


    protected function parseModel(): void
    {
        $this->parsedData['shop'] = $this->parseShop($this->auroraModelData->{'Website Store Key'});

        $status = match ($this->auroraModelData->{'Website Status'}) {
            'Maintenance' => 'maintenance',
            'Closed' => 'closed',
            default => 'construction',
        };


        $domain = preg_replace('/^www\./', '', strtolower($this->auroraModelData->{'Website URL'}));

        $code=strtolower($this->auroraModelData->{'Website Code'});
        $code=preg_replace('/\.com$/','',$code);
        $code=preg_replace('/\.eu$/','',$code);
        $code=preg_replace('/\.biz$/','',$code);

        $this->parsedData['website'] =
            [

                'name'        => $this->auroraModelData->{'Website Name'},
                'code'        => $code,
                'domain'      => $domain,
                'status'      => $status,
                'launched_at' => $this->parseDate($this->auroraModelData->{'Website Launched'}),
                'created_at'  => $this->parseDate($this->auroraModelData->{'Website From'}),
                'source_id'   => $this->auroraModelData->{'Website Key'},

            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Website Dimension')
            ->where('Website Key', $id)->first();
    }

}
