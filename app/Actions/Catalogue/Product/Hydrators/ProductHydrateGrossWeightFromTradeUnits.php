<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 09 Sept 2024 17:15:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Models\Catalogue\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateGrossWeightFromTradeUnits
{
    use AsAction;

    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->product->id))->dontRelease()];
    }

    public function handle(Product $product): void
    {
        $changed = false;
        $weight  = 0;

        foreach ($product->tradeUnits as $tradeUnit) {
            if (is_numeric($tradeUnit->gross_weight) and is_numeric($tradeUnit->pivot->quantity)) {
                $changed = true;
                $weight += $tradeUnit->gross_weight * $tradeUnit->pivot->quantity;
            }
        }

        if (!$changed) {
            $weight = null;
        }


        $product->updateQuietly(
            [
                'weight' => $weight
            ]
        );

    }


}
