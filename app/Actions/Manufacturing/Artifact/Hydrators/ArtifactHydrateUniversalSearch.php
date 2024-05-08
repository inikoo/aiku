<?php

namespace App\Actions\Manufacturing\Artifact\Hydrators;

use App\Models\Manufacturing\Artifact;
use Lorisleiva\Actions\Concerns\AsAction;

class ArtifactHydrateUniversalSearch
{
    use AsAction;


    public function handle(Artifact $artifact): void
    {
        $artifact->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $artifact->group_id,
                'organisation_id'   => $artifact->organisation_id,
                'organisation_slug' => $artifact->organisation->slug,
                'section'           => 'manufacture',
                'title'             => trim($artifact->name.' '.$artifact->code),
                'description'       => ''
            ]
        );
    }

}
