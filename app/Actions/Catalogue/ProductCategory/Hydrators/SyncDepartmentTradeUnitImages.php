<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:34:14 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Models\Catalogue\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncDepartmentTradeUnitImages
{
    use AsAction;

    public function handle(ProductCategory $department): ProductCategory
    {
        $images = [];


        foreach ($department->tradeUnits as $tradeUnit) {
            foreach ($tradeUnit->media as $media) {
                $images[$media->id] = [
                    'owner_type' => 'TradeUnit',
                    'owner_id'   => $tradeUnit->id,
                    'type'       => 'image'
                ];
            }
        }
        /*
         * To this to work delete TradeUnit images should delete this record as well
         */

        $department->images()->syncWithoutDetaching($images);

        DepartmentInitialiseImageID::run($department);


        return $department;
    }
}
