<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\WorkingPlace\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\HumanResources\Workplace;
use Lorisleiva\Actions\Concerns\AsAction;

class WorkingPlaceHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(Workplace $workplace): void
    {
        $workplace->universalSearch()->create(
            [
                'section' => 'HumanResources',
                'route' => $this->routes(),
                'icon' => 'fa-money-check-alt',
                'primary_term'   => $workplace->name,
                'secondary_term' => $workplace->type
            ]
        );
    }

}
