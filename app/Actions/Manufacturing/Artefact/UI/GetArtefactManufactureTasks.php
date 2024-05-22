<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 May 2024 17:29:22 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artefact\UI;

use App\Http\Resources\Manufacturing\ManufactureTasksResource;
use App\Models\Manufacturing\Artefact;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetArtefactManufactureTasks
{
    use AsObject;

    public function handle(Artefact $artefact, ActionRequest $request): array
    {
        // Fetch the artefacts related to the manufacture task from the pivot table
        $manufactureTasks = $artefact->manufactureTasks()->get();


        $manufactureTaskData = ManufactureTasksResource::collection($manufactureTasks)->toArray($request);

        return $manufactureTaskData;
    }
}
