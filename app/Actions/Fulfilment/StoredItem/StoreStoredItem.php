<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateFulfilment;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Models\CRM\Customer;
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

    public Customer $customer;

    public function handle(Customer $customer, array $modelData): StoredItem
    {
        /** @var StoredItem $storedItem */
        $storedItem = $customer->storedItems()->create($modelData);
        CustomerHydrateStoredItems::dispatch($customer);
        OrganisationHydrateFulfilment::dispatch(app('currentTenant'));

        return $storedItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.edit");
    }


    public function rules(): array
    {
        return [
            'reference'   => ['required', 'unique:stored_items', 'between:2,9', 'alpha'],
            'type'        => ['required', Rule::in(StoredItemTypeEnum::values())],
            'location_id' => ['required', 'exists:tenant.locations,id']
        ];
    }

    public function asController(Customer $customer, ActionRequest $request): StoredItem
    {
        $this->customer = $customer;
        $mergedArray    = array_merge($request->all(), [
            'location_id' => $request->input('location')['id']
        ]);
        $this->setRawAttributes($mergedArray);

        return $this->handle($customer, $this->validateAttributes());
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('fulfilment.stored-items.show', $storedItem->slug);
    }
}
