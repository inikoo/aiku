<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\Hydrators;

use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(Stock $stock): void
    {

        if($stock->trashed()) {
            return;
        }

        $stock->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'    => $stock->group_id,
                'section'     => 'goods',
                'title'       => trim($stock->code.' '.$stock->name),
                'description' => ''
            ]
        );
    }

}
