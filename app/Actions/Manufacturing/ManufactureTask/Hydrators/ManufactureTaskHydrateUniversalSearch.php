<?php

namespace App\Actions\Manufacturing\ManufactureTask\Hydrators;

use App\Models\Manufacturing\ManufactureTask;
use Lorisleiva\Actions\Concerns\AsAction;

class ManufactureTaskHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(ManufactureTask $manufactureTask): void
    {
        $manufactureTask->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $manufactureTask->group_id,
                'organisation_id'   => $manufactureTask->organisation_id,
                'organisation_slug' => $manufactureTask->organisation->slug,
                'sections'          => ['manufacture'],
                'haystack_tier_1'   => trim($manufactureTask->name.' '.$manufactureTask->code),
            ]
        );
    }

}
