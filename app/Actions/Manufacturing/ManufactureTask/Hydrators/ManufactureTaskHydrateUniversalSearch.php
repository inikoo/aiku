<?php

namespace App\Actions\Manufacturing\ManufactureTask\Hydrators;

use App\Models\Manufacturing\ManufactureTask;
use App\Models\Manufacturing\RawMaterial;
use Lorisleiva\Actions\Concerns\AsAction;

class ManufactureTaskHydrateUniversalSearch
{
    use AsAction;


    public function handle(ManufactureTask $manufactureTask): void
    {
        $manufactureTask->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $manufactureTask->group_id,
                'organisation_id'   => $manufactureTask->organisation_id,
                'organisation_slug' => $manufactureTask->organisation->slug,
                'section'           => 'manufacture',
                'title'             => trim($manufactureTask->name.' '.$manufactureTask->code),
                'description'       => ''
            ]
        );
    }

}
