<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\Fulfilment\Pallet\Hydrators\HydrateQuantityPalletStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFulfilment;
use App\Enums\Fulfilment\StoredItem\StoredItemTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncPalletStoredItem
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(Pallet $pallet, array $modelData): Pallet
    {
        $pallet->storedItems()->sync($modelData['stored_item_id']);

        HydrateQuantityPalletStoredItems::dispatch($pallet);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.edit");
    }


    public function rules(): array
    {
        return [
            'stored_item_id'   => ['required', 'array']
        ];
    }

    public function asController(Customer $customer, ActionRequest $request): Pallet
    {
        $this->customer = $customer;
        $mergedArray    = array_merge($request->all(), [
            'location_id' => $request->input('location')['id']
        ]);
        $this->setRawAttributes($mergedArray);

        return $this->handle($customer, $this->validateAttributes());
    }
}
