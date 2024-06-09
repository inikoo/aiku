<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

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
