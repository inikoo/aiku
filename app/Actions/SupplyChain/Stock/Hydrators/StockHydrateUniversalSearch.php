<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 10:36:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Stock\Hydrators;

use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateUniversalSearch
{
    use AsAction;


    public function handle(Stock $stock): void
    {
        $stock->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'supply-chain',
                'title'       => trim($stock->code.' '.$stock->description),
                'description' => ''
            ]
        );
    }

}
