<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\Hydrators;

use App\Models\SupplyChain\Stock;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fill image_id if id null and stocks has images (to be run after trade units set up or first image added)
 */
class StockInitialiseImageID implements ShouldBeUnique
{
    use AsAction;


    public function handle(Stock $stock): void
    {
        if ($stock->images()->count()) {
            if ($stock->image_id) {
                return;
            }

            $image = $stock->images()->first();

            if ($image) {
                $stock->update(
                    [
                        'image_id' => $image->id
                    ]
                );
            }
        }
    }

    public function getJobUniqueId(Stock $stock): string
    {
        return $stock->id;
    }
}
