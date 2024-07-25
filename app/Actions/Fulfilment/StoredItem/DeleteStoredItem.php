<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItems;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateWithStoredItems;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItems;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\ActionRequest;

class DeleteStoredItem extends OrgAction
{
    use WithActionUpdate;

    public function handle(StoredItem $storedItem, array $modelData): StoredItem
    {
        foreach ($storedItem->pallets as $pallet) {
            // todo , delete pallet_stored_items pivot table

            PalletHydrateWithStoredItems::run($pallet); // !important this must be ::run
            PalletHydrateStoredItems::run($pallet);
        }

        $group             =$storedItem->group;
        $organisation      =$storedItem->organisation;
        $fulfilment        =$storedItem->fulfilment;
        $fulfilmentCustomer=$storedItem->fulfilmentCustomer;
        $warehouse         =$storedItem->warehouse;

        $storedItem->delete();

        GroupHydrateStoredItems::dispatch($group);
        OrganisationHydrateStoredItems::dispatch($organisation);
        FulfilmentHydrateStoredItems::dispatch($fulfilment);
        FulfilmentCustomerHydrateStoredItems::dispatch($fulfilmentCustomer);



        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->initialisationFromFulfilment($storedItem->fulfilment, $request);

        return $this->handle($storedItem, $this->validatedData);
    }

    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }
}
