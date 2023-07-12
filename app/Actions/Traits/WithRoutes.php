<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:27:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Traits;

trait WithRoutes
{
    public function routes(): array
    {
        return [
            'name'      => request()->route()->getName(),
            'arguments' => request()->route()->originalParameters(),
            'url'       => request()->path()
        ];
    }
}
