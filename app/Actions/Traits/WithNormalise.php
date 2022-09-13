<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 22:47:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use Illuminate\Support\Collection;

trait WithNormalise{

    function normalise(Collection $shares): array
    {
        $total = $shares->sum();

        $normalisedShares = $shares->mapWithKeys(function ($share, $key) use ($total) {
            return [$key => $share / $total];
        });

        return $normalisedShares->all();
    }

}

