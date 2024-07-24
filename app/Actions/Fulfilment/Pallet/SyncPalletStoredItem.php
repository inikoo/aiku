<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\Pallet\Hydrators\HydrateQuantityPalletStoredItems;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Pallet;
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

        HydrateQuantityPalletStoredItems::run($pallet);

        return $pallet;
    }

    public function rules(): array
    {
        return [
            'stored_item_id'   => ['required', 'array']
        ];
    }

    public function asController(Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->setRawAttributes($request->all());

        return $this->handle($pallet, $this->validateAttributes());
    }
}
