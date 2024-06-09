<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraStockFamily extends FetchAurora
{
    protected function parseModel(): void
    {


        $code=$this->auroraModelData->{'Category Code'};

        if($code=='') {
            return;
        }


        $code=preg_replace('/\(BOX\)$/', '-BOX', $code);
        $code=preg_replace('/\s+/', '-', $code);

        $sourceSlug = Str::kebab(strtolower($code));

        $this->parsedData['stock_family'] = [
            'code'        => $code,
            'name'        => $this->auroraModelData->{'Category Label'},
            'state'       => match ($this->auroraModelData->{'Part Category Status'}) {
                'InUse'         => 'active',
                'Discontinuing' => 'discontinuing',
                'NotInUse'      => 'discontinued',
                default         => 'in-process',
            },
            'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Category Key'},
            'source_slug' => $sourceSlug
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Category Dimension')
            ->leftJoin('Part Category Dimension', 'Part Category Key', 'Category Key')
            ->where('Category Key', $id)->first();
    }
}
