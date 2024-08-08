<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncStoredItemPallet extends OrgAction
{
    use AsAction;
    use WithAttributes;

    protected FulfilmentCustomer $fulfilmentCustomer;
    protected Fulfilment $fulfilment;

    public function handle(StoredItem $storedItem, array $modelData): void
    {
        $storedItem->pallets()->sync(Arr::get($modelData, 'pallets', []));
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'pallets'            => ['sometimes', 'array'],
            'pallets.*.quantity' => ['required', 'integer', 'min:1']
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'pallets.*.quantity.required' => __('The quantity is required'),
            'pallets.*.quantity.integer'  => __('The quantity must be an integer'),
            'pallets.*.quantity.min'      => __('The quantity must be at least 1'),
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        foreach ($request->input('pallets') as $pallet) {
            $this->set('pallets', [$pallet['pallet'] => [
                'quantity' => $pallet['quantity']
            ]]);
        }
    }

    public function asController(StoredItem $storedItem, ActionRequest $request): void
    {
        $this->fulfilmentCustomer = $storedItem->fulfilmentCustomer;
        $this->fulfilment         = $storedItem->fulfilment;

        $this->initialisation($storedItem->organisation, $request);

        $this->handle($storedItem, $this->validatedData);
    }
}
