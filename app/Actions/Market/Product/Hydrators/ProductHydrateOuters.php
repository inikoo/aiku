<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Market\Outer\OuterStateEnum;
use App\Models\Market\Outer;
use App\Models\Market\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateOuters
{
    use AsAction;
    use WithEnumStats;
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


        $stats         = [
            'number_outers' => $product->outers()->count(),
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outers',
                field: 'state',
                enum: OuterStateEnum::class,
                models: Outer::class,
                where: function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                }
            )
        );

        $product->stats()->update($stats);
    }

}
