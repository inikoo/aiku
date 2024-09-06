<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 14:28:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraHistoricSupplierProduct extends FetchAurora
{
    protected function parseModel(): void
    {
        $supplierProduct = $this->parseSupplierProduct($this->organisation->id.':'.$this->auroraModelData->{'Supplier Part Historic Supplier Part Key'});

        if (!$supplierProduct) {
            return;
        }
        $this->parsedData['supplier_product']=$supplierProduct;

        $status = 0;
        if (DB::connection('aurora')->table('Supplier Part Dimension')->where('Supplier Part Historic Key', '=', $this->auroraModelData->{'Supplier Part Historic Key'})->exists()) {
            $status = 1;
        }


        $units = $this->auroraModelData->{'Supplier Part Historic Units Per Package'};
        if ($units == 0) {
            $units = 1;
        }

        $this->parsedData['historic_supplier_product'] = [
            'code'      => $this->auroraModelData->{'Supplier Part Historic Reference'},
            'unit_cost' => $this->auroraModelData->{'Supplier Part Historic Unit Cost'},
            'units'     => $units,
            'status'    => $status,
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Supplier Part Historic Key'}
        ];
    }


    protected function fetchData($id): object|null
    {

        return DB::connection('aurora')
            ->table('Supplier Part Historic Dimension')
            ->leftJoin(
                'Supplier Part Dimension',
                'Supplier Part Dimension.Supplier Part Key',
                '=',
                'Supplier Part Historic Dimension.Supplier Part Historic Supplier Part Key'
            )
            ->where('Supplier Part Historic Dimension.Supplier Part Historic Key', $id)->first();
    }
}
