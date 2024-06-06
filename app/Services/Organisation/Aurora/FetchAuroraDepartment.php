<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:00:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\Utils\Abbreviate;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraDepartment extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['shop'] = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Product Category Store Key'});

        $code = $this->cleanTradeUnitReference($this->auroraModelData->{'Category Code'});

        if($code=='40%') {
            $code='40off';
        }

        if(strlen($code) > 32) {
            $code =Abbreviate::run($code, 32);
        }


        $this->parsedData['department'] = [
            'type'                  => ProductCategoryTypeEnum::DEPARTMENT,
            'code'                  => $code,
            'name'                  => $this->auroraModelData->{'Category Label'},
            'source_department_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Category Key'},
        ];

        $createdAt= $this->parseDate($this->auroraModelData->{'Product Category Valid From'});
        if($createdAt) {
            $this->parsedData['department']['created_at'] = $createdAt;
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
