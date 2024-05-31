<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 03:04:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraTradeUnit extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;

    protected function parseModel(): void
    {

        $reference  = $this->cleanTradeUnitReference($this->auroraModelData->{'Part Reference'});


        $sourceSlug = Str::lower($reference);


        $name=$this->auroraModelData->{'Part Recommended Product Unit Name'};
        if($name=='') {
            $name=$reference;
        }


        $this->parsedData['trade_unit'] = [
            'name'        => $name,
            'code'        => $reference,
            'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Part SKU'},
            'source_slug' => $sourceSlug
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }





}
