<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-15m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\StoredItem;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateStoredItems;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateWithStoredItems;
use App\Actions\Fulfilment\StoredItem\Search\StoredItemRecordSearch;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItems;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreRetinaStoredItem extends RetinaAction
{
    use AsAction;
    use WithAttributes;

    public FulfilmentCustomer $fulfilmentCustomer;

    public function handle(FulfilmentCustomer $parent, array $modelData): StoredItem
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'fulfilment_id', $parent->fulfilment_id);

        /** @var StoredItem $storedItem */
        $storedItem = $parent->storedItems()->create($modelData);

        GroupHydrateStoredItems::dispatch($parent->group);
        OrganisationHydrateStoredItems::dispatch($parent->organisation);
        FulfilmentHydrateStoredItems::dispatch($parent->fulfilment);
        FulfilmentCustomerHydrateStoredItems::dispatch($storedItem->fulfilmentCustomer);



        StoredItemRecordSearch::dispatch($storedItem);

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        return [
            'reference'    => ['required', 'max:128',  new AlphaDashDotSpaceSlashParenthesisPlus(),
             new IUnique(
                 table: 'stored_items',
                 extraConditions: [
                     ['column' => 'fulfilment_customer_id', 'value' => $this->fulfilmentCustomer->id],
                 ]
             )
            ]
        ];
    }

    public function asController(ActionRequest $request): StoredItem
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->fulfilment         = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request);

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.fulfilment.stored-items.show', $storedItem->slug); //TODO: put the right route
    }
}
