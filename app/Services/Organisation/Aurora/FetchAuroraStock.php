<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:11:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraStock extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['units_per_package'] = $this->auroraModelData->{'Part Units Per Package'};

        $this->parsedData['stock'] = [
            'description'            => $this->auroraModelData->{'Part Recommended Product Unit Name'},
            'code'                   => strtolower($this->auroraModelData->{'Part Reference'}),
            'organisation_source_id' => $this->auroraModelData->{'Part SKU'},
            'created_at'             => $this->auroraModelData->{'Part Valid From'} ?? null,
            'activated_at'           => $this->auroraModelData->{'Part Active From'} ?? null,
            'discontinued_at'        => ($this->auroraModelData->{'Part Valid To'} && $this->auroraModelData->{'Part Status'} == 'Not In Use') ? $this->auroraModelData->{'Part Valid To'} : null,


            'state' => match ($this->auroraModelData->{'Part Status'}) {
                'In Use' => 'active',
                'Discontinuing' => 'discontinuing',
                'In Process' => 'in-process',
                'Not In Use' => 'discontinued'
            }
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }

}
