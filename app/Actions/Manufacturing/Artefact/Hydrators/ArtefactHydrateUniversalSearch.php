<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 18:42:52 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artefact\Hydrators;

use App\Models\Manufacturing\Artefact;
use Lorisleiva\Actions\Concerns\AsAction;

class ArtefactHydrateUniversalSearch
{
    use AsAction;


    public function handle(Artefact $artefact): void
    {
        $artefact->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $artefact->group_id,
                'organisation_id'   => $artefact->organisation_id,
                'organisation_slug' => $artefact->organisation->slug,
                'section'           => 'manufacture',
                'title'             => trim($artefact->name.' '.$artefact->code),
                'description'       => ''
            ]
        );
    }

}
