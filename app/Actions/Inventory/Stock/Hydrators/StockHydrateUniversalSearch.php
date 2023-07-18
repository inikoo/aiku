<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Stock $stock): void
    {
        $stock->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'inventory',
                'title'       => trim($stock->code.' '.$stock->description),
                'description' => ''
            ]
        );
    }

}
