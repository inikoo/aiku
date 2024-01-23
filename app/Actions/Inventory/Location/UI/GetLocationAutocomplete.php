<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:30:45 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLocationAutocomplete
{
    use AsAction;

    public function handle(Organisation $organisation, ActionRequest $request): AnonymousResourceCollection
    {
        $locations = Location::where('slug', 'like', $request->get('q'))->get();

        return LocationResource::collection($locations);
    }
}
