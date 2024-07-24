<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgStockHydrateUniversalSearch
{
    use AsAction;
    public string $jobQueue = 'universal-search';

    public function handle(OrgStock $orgStock): void
    {
        $orgStock->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'                    => $orgStock->group_id,
                'organisation_id'             => $orgStock->organisation_id,
                'organisation_slug'           => $orgStock->organisation->slug,
                'sections'                    => ['inventory'],
                'haystack_tier_1'             => trim($orgStock->stock->code.' '.$orgStock->stock->name),
            ]
        );
    }

}
