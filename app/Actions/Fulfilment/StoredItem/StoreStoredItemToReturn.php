<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydratePallets;
use App\Actions\OrgAction;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreStoredItemToReturn extends OrgAction
{
    use AsCommand;

    public $commandSignature = 'stored-item:store-to-return {palletReturn}';
    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $storedItemModels = Arr::get($modelData, 'stored_items');
        $currentQuantity  = 0;

        $storedItems = $palletReturn->fulfilmentCustomer->storedItems()->whereIn('id', array_keys($storedItemModels))->get();
        foreach ($storedItems as $value) {
            /** @var StoredItem $storedItem */
            $storedItem = $value;

            $pallets           = $storedItem->pallets;
            $requiredQuantity  = Arr::get($storedItemModels, $value->id)['quantity'];

            foreach ($pallets as $pallet) {
                $remainingQuantity   = $requiredQuantity - $currentQuantity;
                $palletStoredItemQty = $pallet->storedItems->sum('pivot.quantity');

                if ($palletStoredItemQty <= $remainingQuantity) {
                    $currentQuantity += $palletStoredItemQty;
                } else {
                    $partialPallet           = clone $pallet;
                    $partialPallet->quantity = $remainingQuantity;
                    $currentQuantity += $remainingQuantity;
                }

                $this->attach($palletReturn, $pallet, $value, $currentQuantity);

                if ($currentQuantity == $requiredQuantity) {
                    break;
                }
            }
        }

        $palletReturn->refresh();

        PalletReturnHydratePallets::run($palletReturn);

        return $palletReturn;
    }

    public function attach(PalletReturn $palletReturn, Pallet $pallet, $value, $currentQuantity): void
    {
        $palletReturn->storedItems()
            ->attach($value->id, [
            'pallet_id'             => $pallet->id,
            'pallet_stored_item_id' => $pallet->pivot->id,
            'quantity_ordered'      => $currentQuantity,
            'type'                  => 'StoredItem'
        ]);
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

    public function rules(): array
    {
        return [
            'stored_items.*.quantity' => ['required', 'integer']
        ];
    }

    // public function afterValidator($validator)
    // {
    //     dd($validator);
    // }

    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        //         dd($request->all());
        $this->parent = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function fromRetina(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->parent       = $palletReturn;
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request->except(['domain', 'website']));
        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }


    public function asCommand(Command $command): int
    {
        $palletReturn = PalletReturn::where('reference', $command->argument('palletDelivery'))->firstOrFail();

        $this->handle($palletReturn, [
            'group_id'               => $palletReturn->group_id,
            'organisation_id'        => $palletReturn->organisation_id,
            'fulfilment_id'          => $palletReturn->fulfilment_id,
            'fulfilment_customer_id' => $palletReturn->fulfilment_customer_id,
            'warehouse_id'           => $palletReturn->warehouse_id,
            'slug'                   => now()->timestamp
        ]);

        echo "Pallet created from delivery: $palletReturn->reference\n";

        return 0;
    }


    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): RedirectResponse
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.pallet-return.stored_item.store' => Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet_returns.show', [
                'organisation'           => $palletReturn->organisation->slug,
                'fulfilment'             => $palletReturn->fulfilment->slug,
                'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
                'palletReturn'           => $palletReturn->slug
            ]),
            default => Redirect::route('retina.storage.pallet-returns.show', [
                'palletReturn'     => $palletReturn->slug
            ])
        };
    }
}
