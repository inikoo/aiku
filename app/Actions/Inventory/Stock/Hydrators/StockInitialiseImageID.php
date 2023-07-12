<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Apr 2023 11:42:49 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\Stock;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fill image_id if id null and stocks has images (to be run after trade units set up or first image added)
 */
class StockInitialiseImageID implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

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
