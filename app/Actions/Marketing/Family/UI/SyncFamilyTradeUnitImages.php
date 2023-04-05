<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:32:58 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\Marketing\Family\Hydrators\FamilyInitialiseImageID;
use App\Models\Marketing\Family;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncFamilyTradeUnitImages
{
    use AsAction;

    public function handle(Family $family): Family
    {
        $images = [];


        foreach ($family->tradeUnits as $tradeUnit) {
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

        $family->images()->syncWithoutDetaching($images);

        FamilyInitialiseImageID::run($family);


        return $family;
    }
}
