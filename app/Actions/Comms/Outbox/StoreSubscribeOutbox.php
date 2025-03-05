<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\Outbox;

use App\Actions\OrgAction;
// use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateLocations;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\Outbox;
use App\Models\Comms\OutBoxHasSubscribers;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreSubscribeOutbox extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $parent, array $modelData)
    {

        $user = Arr::get($modelData, 'user_id');

        foreach ($user as $key => $value) {
            OutBoxHasSubscribers::create([
                'outbox_id' => $parent->id,
                'user_id' => $value,
                'organisation_id' => $parent->organisation_id,
                'group_id' => $parent->group_id,
                'external_links' => json_encode(Arr::get($modelData, 'external_links')[$key])
            ]);
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("locations.{$this->warehouse->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'user_id'       => [
                'required',
                'array',
            ],
            'external_links' => [
                'required_if:external_links,null',
                'array',
            ],
        ];

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Outbox $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true)
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisationFromFulfilment($parent->fulfilment, $modelData);

        $this->handle($parent, $this->validatedData);
    }

    // public function htmlResponse(Location $location): RedirectResponse
    // {
    //     if (!$location->warehouse_area_id) {
    //         return Redirect::route('grp.org.warehouses.show.infrastructure.locations.show', [
    //             $location->organisation->slug,
    //             $location->warehouse->slug,
    //             $location->slug
    //         ]);
    //     } else {
    //         return Redirect::route('grp.org.warehouses.show.infrastructure.warehouse_areas.show.locations.show', [
    //             $location->organisation->slug,
    //             $location->warehouse->slug,
    //             $location->warehouseArea->slug,
    //             $location->slug
    //         ]);
    //     }
    // }

}
