<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:38:36 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraSupplierProduct extends FetchAurora
{
    protected function parseModel(): void
    {



        $supplier = $this->parseSupplier($this->auroraModelData->{'Supplier Part Supplier Key'});

        if(!$supplier) {
            return;
        }

        $this->parsedData['trade_unit']=$this->parseTradeUnit($this->auroraModelData->{'Supplier Part Part SKU'});


        $this->parsedData['supplier'] =$supplier;

        $sharedData = [];
        $settings   = [];

        $status = true;
        if ($this->auroraModelData->{'Supplier Part Status'} == 'NoAvailable') {
            $status = false;
        }
        $state = match ($this->auroraModelData->{'Supplier Part Status'}) {
            'Discontinued', 'NoAvailable' =>SupplierProductStateEnum::DISCONTINUED,
            default        => SupplierProductStateEnum::ACTIVE,
        };

        if ($state==SupplierProductStateEnum::DISCONTINUED) {
            $status = false;
        }

        if ($this->auroraModelData->{'Supplier Part From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $this->auroraModelData->{'Supplier Part From'};
        }

        $sharedData['raw_price'] = $this->auroraModelData->{'Supplier Part Unit Cost'} ?? 0;


        $stock_quantity_status = match ($this->auroraModelData->{'Part Stock Status'}) {
            'Out_Of_Stock', 'Error' => 'out-of-stock',
            default => strtolower($this->auroraModelData->{'Part Stock Status'})
        };


        $this->parsedData['supplierProduct'] =
            [
                'code' => $this->auroraModelData->{'Supplier Part Reference'},
                'name' => $this->auroraModelData->{'Supplier Part Description'},

                'cost'             => round($this->auroraModelData->{'Supplier Part Unit Cost'} ?? 0, 2),
                'units_per_pack'   => $this->auroraModelData->{'Part Units Per Package'},
                'units_per_carton' => $this->auroraModelData->{'Supplier Part Packages Per Carton'} * $this->auroraModelData->{'Part Units Per Package'},


                'status'                => $status,
                'state'                 => $state,
                'stock_quantity_status' => $stock_quantity_status,

                'shared_data' => $sharedData,
                'settings'    => $settings,
                'created_at'  => $created_at,
                'source_id'   => $this->auroraModelData->{'Supplier Part Key'}
            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Supplier Part Dimension')
            ->leftjoin('Part Dimension', 'Supplier Part Part SKU', 'Part SKU')
            ->where('Supplier Part Key', $id)->first();
    }
}
