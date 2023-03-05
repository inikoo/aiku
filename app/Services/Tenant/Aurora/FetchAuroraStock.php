<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:11:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Actions\SourceFetch\Aurora\FetchStockFamilies;
use Illuminate\Support\Facades\DB;

class FetchAuroraStock extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['stock'] = [
            'description'     => $this->auroraModelData->{'Part Recommended Product Unit Name'},
            'stock_family_id' => $this->getStockFamilyId($this->auroraModelData->{'Part SKU'}),
            'code'            => $this->auroraModelData->{'Part Reference'},
            'source_id'       => $this->auroraModelData->{'Part SKU'},
            'created_at'      => $this->parseDate($this->auroraModelData->{'Part Valid From'}),
            'activated_at'    => $this->parseDate($this->auroraModelData->{'Part Active From'}),
            'units_per_pack'  => $this->auroraModelData->{'Part Units Per Package'},
            'discontinued_at' =>
                ($this->auroraModelData->{'Part Valid To'} && $this->auroraModelData->{'Part Status'} == 'Not In Use')
                    ? $this->parseDate($this->auroraModelData->{'Part Valid To'})
                    :
                    null,
            'state'           => match ($this->auroraModelData->{'Part Status'}) {
                'In Use'        => 'active',
                'Discontinuing' => 'discontinuing',
                'In Process'    => 'in-process',
                'Not In Use'    => 'discontinued'
            }
        ];
    }

    private function getStockFamilyId($sourceID)
    {
        $stockFamilyId = null;

        if ($row = DB::connection('aurora')
            ->table('Category Bridge as B')
            ->leftJoin('Category Dimension as C', 'C.Category Key', 'B.Category Key')
            ->select('B.Category Key')
            ->where('Category Branch Type', 'Head')
            ->where('Subject Key', $sourceID)
            ->where('Subject', 'Part')->first()) {
            $stockFamily   = FetchStockFamilies::run($this->tenantSource, $row->{'Category Key'});
            $stockFamilyId = $stockFamily->id;
        }

        return $stockFamilyId;
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Part Dimension')
            ->where('Part SKU', $id)->first();
    }
}
