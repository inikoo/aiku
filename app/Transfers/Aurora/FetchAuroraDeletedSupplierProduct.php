<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraDeletedSupplierProduct extends FetchAurora
{
    protected function parseModel(): void
    {
        $deleted_at        = $this->parseDate($this->auroraModelData->{'Supplier Part Deleted Date'});
        $auroraDeletedData = json_decode(gzuncompress($this->auroraModelData->{'Supplier Part Deleted Metadata'}));


        $supplierSourceSlug = Str::kebab(strtolower($auroraDeletedData->{'Supplier Code'}));
        $supplierSourceSlug .= '-deleted';


        $this->parsedData['supplier'] = $this->parseSupplier(
            $supplierSourceSlug,
            $this->organisation->id.':'.$auroraDeletedData->{'Supplier Part Supplier Key'}
        );
        if (!$this->parsedData['supplier']) {
            return;
        }
        if (!$auroraDeletedData->{'Supplier Part Part SKU'}) {
            return;
        }
        $stock = $this->parseStock($auroraDeletedData->{'Supplier Part Part SKU'});
        if (!$stock) {
            return;
        }

        $data       = [];
        $settings   = [];

        $status = true;
        if ($auroraDeletedData->{'Supplier Part Status'}=='NoAvailable') {
            $status = false;
        }



        $state = match ($auroraDeletedData->{'Supplier Part Status'}) {
            'Discontinued', 'NoAvailable' =>SupplierProductStateEnum::DISCONTINUED,
            default        => SupplierProductStateEnum::ACTIVE,
        };

        if ($state==SupplierProductStateEnum::DISCONTINUED) {
            $status = false;
        }


        if ($auroraDeletedData->{'Supplier Part From'} == '0000-00-00 00:00:00') {
            $created_at = null;
        } else {
            $created_at = $auroraDeletedData->{'Supplier Part From'};
        }

        $data['original_uniy_cost'] = $auroraDeletedData->{'Supplier Part Unit Cost'} ?? 0;




        $this->parsedData['supplierProduct'] =
            [
                'code' => $auroraDeletedData->{'Supplier Part Reference'},
                'name' => $auroraDeletedData->{'Supplier Part Description'},

                'cost'             => round($auroraDeletedData->{'Supplier Part Unit Cost'} ?? 0, 2),
                'units_per_pack'   => $stock->units_per_pack,
                'units_per_carton' => $auroraDeletedData->{'Supplier Part Packages Per Carton'} * $stock->units_per_pack,


                'status'                => $status,
                'state'                 => $state,
                'stock_quantity_status' => 'no-applicable',
                'deleted_at'            => $deleted_at,

                'data'        => $data,
                'settings'    => $settings,
                'created_at'  => $created_at,
                'source_id'   => $this->organisation->id.':'.$auroraDeletedData->{'Supplier Part Key'}
            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Supplier Part Deleted Dimension')
            ->where('Supplier Part Deleted Key', $id)->first();
    }
}
