<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Family;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class FamilyHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Family $family): void
    {
        $family->universalSearch()->create(
            [
                'primary_term'   => $family->name,
                'secondary_term' => $family->code
            ]
        );
    }

    public function getJobUniqueId(Family $family): int
    {
        return $family->id;
    }
}
