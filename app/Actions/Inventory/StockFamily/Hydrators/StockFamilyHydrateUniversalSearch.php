<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(StockFamily $stockFamily): void
    {
        $stockFamily->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'inventory',
                'title'       => join(' ', array_unique([$stockFamily->code, $stockFamily->name])),
                'description' => ''
            ]
        );
    }

}
