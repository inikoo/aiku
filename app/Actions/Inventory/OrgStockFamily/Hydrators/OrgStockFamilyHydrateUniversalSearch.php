<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 03 Jun 2024 19:41:59 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily\Hydrators;

use App\Models\Inventory\OrgStockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockFamilyHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(OrgStockFamily $orgStockFamily): void
    {
        $orgStockFamily->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $orgStockFamily->group_id,
                'organisation_id'   => $orgStockFamily->organisation_id,
                'organisation_slug' => $orgStockFamily->organisation->slug,
                'sections'          => ['inventory'],
                'haystack_tier_1'   => trim($orgStockFamily->code.' '.$orgStockFamily->name),

            ]
        );
    }

}
