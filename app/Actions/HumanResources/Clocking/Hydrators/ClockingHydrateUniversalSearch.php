<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\Hydrators;

use App\Actions\Traits\WithRoutes;
use App\Actions\Traits\WithTenantJob;
use App\Models\HumanResources\Clocking;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(Clocking $clocking): void
    {
        $clocking->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'HumanResources',
                'route'          => $this->routes(),
                'icon'           => 'fa-clock',
                'title'          => $clocking->slug,
            ]
        );
    }

}
