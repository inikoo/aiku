<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:11:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceFetch\Aurora\FetchAuroraStockFamilies;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraStock extends FetchAurora
{
    use WithAuroraParsers;

    protected function parseModel(): void
    {
        $tradeUnitReference = $this->cleanTradeUnitReference($this->auroraModelData->{'Part Reference'});
        $tradeUnitSlug      = Str::lower($tradeUnitReference);


        $this->parsedData['trade_unit'] = $this->parseTradeUnit(
            $tradeUnitSlug,
            $this->auroraModelData->{'Part SKU'}
        );

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Part Reference'});


        $sourceSlug = Str::kebab(strtolower($code));

        $name = $this->auroraModelData->{'Part Recommended Product Unit Name'};
        if ($name == '') {
            $name = $this->auroraModelData->{'Part Package Description'};
        }
        if ($name == '') {
            $name = 'Not set';
        }


        $this->parsedData['stock'] = [
            'name'            => $name,
            'stock_family_id' => $this->getStockFamilyId($this->auroraModelData->{'Part SKU'}),
            'code'            => $code,
            'created_at'      => $this->parseDate($this->auroraModelData->{'Part Valid From'}),
            'activated_at'    => $this->parseDate($this->auroraModelData->{'Part Active From'}),
            'units_per_pack'  => $this->auroraModelData->{'Part Units Per Package'},
            'unit_value'      => $this->auroraModelData->{'Part Cost in Warehouse'},
            'discontinued_at' =>
                ($this->auroraModelData->{'Part Valid To'} && $this->auroraModelData->{'Part Status'} == 'Not In Use')
                    ? $this->parseDate($this->auroraModelData->{'Part Valid To'})
                    :
                    null,
            'state'           => match ($this->auroraModelData->{'Part Status'}) {
                'In Use' => StockStateEnum::ACTIVE,
                'Discontinuing', 'Not In Use' => StockStateEnum::DISCONTINUED,
                'In Process' => StockStateEnum::IN_PROCESS,
            },


            'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Part SKU'},
            'source_slug' => $sourceSlug
        ];

        $this->parsedData['org_stock'] = [

            'state' => match ($this->auroraModelData->{'Part Status'}) {
                'In Use'        => OrgStockStateEnum::ACTIVE,
                'Discontinuing' => OrgStockStateEnum::DISCONTINUING,
                'Not In Use'    => OrgStockStateEnum::DISCONTINUED,
                default         => null
            },


            'quantity_status' => match ($this->auroraModelData->{'Part Stock Status'}) {
                'Surplus'      => 'excess',
                'Optimal'      => 'ideal',
                'Low'          => 'low',
                'Critical'     => 'critical',
                'Out_Of_Stock' => 'out-of-stock',
                'Error'        => 'error',
            },
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Part SKU'},
            'source_slug'     => $sourceSlug
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
            $stockFamily   = FetchAuroraStockFamilies::run($this->organisationSource, $row->{'Category Key'});
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
