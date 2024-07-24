<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\Production\Hydrators;

use App\Models\Manufacturing\Production;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductionHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Production $production): void
    {
        $production->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'                    => $production->group_id,
                'organisation_id'             => $production->organisation_id,
                'organisation_slug'           => $production->organisation->slug,
                'sections'                    => ['manufacture'],
                'haystack_tier_1'             => trim($production->name.' '.$production->code),
            ]
        );
    }

}
