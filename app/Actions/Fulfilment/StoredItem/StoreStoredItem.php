<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStoredItem
{
    use AsAction;
    use WithAttributes;

    public FulfilmentCustomer $fulfilmentCustomer;
    private Fulfilment $fulfilment;

    public function handle(FulfilmentCustomer|Pallet $parent, array $modelData): StoredItem
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        $modelData['type'] = StoredItemTypeEnum::PALLET;

        /** @var StoredItem $storedItem */
        $storedItem = $parent->storedItems()->create($modelData);

        if($parent instanceof FulfilmentCustomer) {
            FulfilmentCustomerHydrateStoredItems::dispatch($parent);
        }

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }


    public function rules(): array
    {
        return [
            'reference'   => ['required', 'unique:stored_items', 'between:2,9', 'alpha'],
            'type'        => ['sometimes', Rule::in(StoredItemTypeEnum::values())],
            'location_id' => ['sometimes', 'exists:locations,id']
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItem
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->fulfilment         = $fulfilmentCustomer->fulfilment;

        $this->setRawAttributes($request->all());

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function fromRetina(ActionRequest $request): StoredItem
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;

        $this->setRawAttributes($request->all());

        $this->fulfilment         = $fulfilmentCustomer->fulfilment;
        $this->fulfilmentCustomer = $fulfilmentCustomer;

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function inPallet(Pallet $pallet, ActionRequest $request): StoredItem
    {
        $this->fulfilmentCustomer = $pallet->fulfilmentCustomer;
        $this->fulfilment         = $pallet->fulfilment;

        $this->setRawAttributes($request->all());

        return $this->handle($pallet, $this->validateAttributes());
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.fulfilment.stored-items.show', $storedItem->slug);
    }
}
