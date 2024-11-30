<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Sept 2024 14:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\Hydrators;

use App\Models\SupplyChain\Stock;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateGrossWeightFromTradeUnits
{
    use AsAction;

    private Stock $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->stock->id))->dontRelease()];
    }

    public function handle(Stock $stock): void
    {
        $changed = false;
        $weight  = 0;

        foreach ($stock->tradeUnits as $tradeUnit) {
            if (is_numeric($tradeUnit->gross_weight) and is_numeric($tradeUnit->pivot->quantity)) {
                $changed = true;
                $weight += $tradeUnit->gross_weight * $tradeUnit->pivot->quantity;
            }
        }

        if (!$changed) {
            $weight = null;
        }


        $stock->updateQuietly(
            [
                'gross_weight' => $weight
            ]
        );

    }


}
