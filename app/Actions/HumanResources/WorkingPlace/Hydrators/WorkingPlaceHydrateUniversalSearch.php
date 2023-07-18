<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\WorkingPlace\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\HumanResources\Workplace;
use Lorisleiva\Actions\Concerns\AsAction;

class WorkingPlaceHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Workplace $workplace): void
    {
        $workplace->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'HumanResources',
                'route'   => json_encode([
                    'name'      => 'hr.working-places.show',
                    'arguments' => [
                        $workplace->slug
                    ]
                ]),
                'icon'           => 'fa-money-check-alt',
                'title'          => $workplace->name,
                'description'    => $workplace->type
            ]
        );
    }

}
