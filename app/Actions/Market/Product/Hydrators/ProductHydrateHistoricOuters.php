<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:35:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Market\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateHistoricOuters
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
            'number_historic_outerables' => $product->historicOuters()->count(),
        ];

        $product->stats()->update($stats);
    }

}
