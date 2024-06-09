<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraDeletedStock extends FetchAurora
{
    protected function parseModel(): void
    {
        $deleted_at = $this->auroraModelData->{'Part Deleted Date'};

        if (!$deleted_at) {
            print "Deleted stock no date\n";

            return;
        }

        $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Part Deleted Metadata'}));


        if ($this->auroraModelData->{'Part Deleted Reference'} == '') {
            $code = 'unknown';
        } else {
            $code = strtolower($this->auroraModelData->{'Part Deleted Reference'});
        }

        $code = $this->cleanTradeUnitReference($code);
        $code.= '-deleted';
        $sourceSlug = Str::kebab(strtolower($code));


        $this->parsedData['stock'] =
            [
                'name'        => $auroraDeletedData->{'Part Recommended Product Unit Name'} ?? $code,
                'code'        => $code,
                'deleted_at'  => $deleted_at,
                'created_at'  => $auroraDeletedData->{'Part Valid From'} ?? null,
                'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Part Deleted Key'},
                'source_slug' => $sourceSlug
            ];



        $this->parsedData['org_stock'] = [
            'state'           => OrgStockStateEnum::DISCONTINUED,
            'quantity_status' => 'out-of-stock',
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Part Deleted Key'},
            'source_slug'     => $sourceSlug
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Deleted Dimension')
            ->where('Part Deleted Key', $id)->first();
    }
}
