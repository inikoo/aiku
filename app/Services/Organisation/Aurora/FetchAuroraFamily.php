<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:35:18 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraFamily extends FetchAurora
{
    protected function parseModel(): void
    {
        $department_id = null;
        $parent        = null;

        if ($this->auroraModelData->{'Product Category Department Category Key'}) {


            $parent        = $this->parseDepartment($this->organisation->id.':'.$this->auroraModelData->{'Product Category Department Category Key'});
            $department_id = $parent?->id;

        }
        if (!$parent) {
            $parent = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Product Category Store Key'});
        }

        $this->parsedData['parent'] = $parent;

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Category Code'});


        $this->parsedData['family'] = [
            'type'             => ProductCategoryTypeEnum::FAMILY,
            'code'             => $code,
            'name'             => $this->auroraModelData->{'Category Label'},
            'department_id'    => $department_id,
            'source_family_id' => $this->organisation->id.':'.$this->auroraModelData->{'Category Key'},
        ];

        $createdAt = $this->parseDate($this->auroraModelData->{'Product Category Valid From'});
        if ($createdAt) {
            $this->parsedData['family']['created_at'] = $createdAt;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Category Dimension')
            ->leftJoin('Product Category Dimension', 'Product Category Key', 'Category Key')
            ->where('Category Key', $id)->first();
    }
}
