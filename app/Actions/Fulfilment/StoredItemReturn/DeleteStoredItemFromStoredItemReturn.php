<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemReturn;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class DeleteStoredItemFromStoredItemReturn extends OrgAction
{
    use WithActionUpdate;


    private StoredItem $storedItem;

    public function handle(StoredItemReturn $storedItemReturn, StoredItem $storedItem): bool
    {
        $storedItemReturn->items()->detach($storedItem->id);

        HydrateFulfilmentCustomer::dispatch($storedItemReturn->fulfilmentCustomer);

        return true;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, StoredItemReturn $storedItemReturn, StoredItem $storedItem, ActionRequest $request): bool
    {
        $this->storedItem = $storedItem;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($storedItemReturn, $storedItem);
    }

    public function fromRetina(StoredItemReturn $storedItemReturn, StoredItem $storedItem, ActionRequest $request): bool
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->storedItem       = $storedItem;
        $fulfilmentCustomer     = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment       = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($storedItemReturn, $storedItem);
    }

    public function action(StoredItemReturn $storedItemReturn, StoredItem $storedItem, array $modelData, int $hydratorsDelay = 0): bool
    {
        $this->storedItem         = $storedItem;
        $this->asAction           = true;
        $this->hydratorsDelay     = $hydratorsDelay;
        $this->initialisationFromFulfilment($storedItemReturn->fulfilment, $modelData);

        return $this->handle($storedItemReturn, $storedItem);
    }

    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }
}
