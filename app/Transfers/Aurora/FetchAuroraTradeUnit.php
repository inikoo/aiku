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
        $reference = $this->cleanTradeUnitReference($this->auroraModelData->{'Part Reference'});


        $sourceSlug = Str::lower($reference);


        $name = $this->auroraModelData->{'Part Recommended Product Unit Name'};
        if ($name == '') {
            $name = $reference;
        }

        $grossWeight = null;
        $netWeight   = null;

        if ($this->auroraModelData->{'Part Package Weight'} > 0) {
            $grossWeight = round(1000 * $this->auroraModelData->{'Part Package Weight'} / $this->auroraModelData->{'Part Units Per Package'});
        }

        if ($this->auroraModelData->{'Part Unit Weight'} > 0) {
            $netWeight = round(1000 * $this->auroraModelData->{'Part Unit Weight'});
        }

//        if($grossWeight && $netWeight){
//            print_r([
//                'name'         => $name,
//                'code'         => $reference,
//                'gross_weight' => $grossWeight,
//                'net_weight'   => $netWeight,
//            ]);
//            $ratio = $grossWeight / $netWeight;
//            dd($ratio);
//        }


        $this->parsedData['trade_unit'] = [
            'name'         => $name,
            'code'         => $reference,
            'source_id'    => $this->organisation->id.':'.$this->auroraModelData->{'Part SKU'},
            'source_slug'  => $sourceSlug,
            'gross_weight' => $grossWeight,
            'net_weight'   => $netWeight,
        ];

        //dd($this->auroraModelData);

    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }


}
