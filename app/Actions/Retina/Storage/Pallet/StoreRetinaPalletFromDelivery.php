<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\Pallet;

use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\Pallet\SyncPalletStoredItem;
use App\Actions\Fulfilment\PalletDelivery\AutoAssignServicesToPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePallets;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\RetinaAction;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreRetinaPalletFromDelivery extends RetinaAction
{
    private bool $action = false;
    private PalletDelivery $parent;

    public function handle(PalletDelivery $palletDelivery, array $modelData): Pallet
    {
        data_set($modelData, 'group_id', $palletDelivery->group_id);
        data_set($modelData, 'organisation_id', $palletDelivery->organisation_id);
        data_set($modelData, 'fulfilment_id', $palletDelivery->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $palletDelivery->fulfilment_customer_id);
        data_set($modelData, 'warehouse_id', $palletDelivery->warehouse_id);
        data_set($modelData, 'pallet_delivery_id', $palletDelivery->id);

        if (Arr::get($modelData, 'type')) {
            data_set($modelData, 'type', Arr::get($modelData, 'type'));
        }

        $totalPallets        = $palletDelivery->fulfilmentCustomer->pallets()->count();
        $numberStoredPallets = $palletDelivery->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();
        $palletLimits        = $palletDelivery->fulfilmentCustomer->rentalAgreement->pallets_limit ?? 0;
        $palletLimitLeft     = ($palletLimits - ($totalPallets + $numberStoredPallets));

        if ($palletLimits > 0 && $palletLimitLeft <= 0) {
            abort(403, __("Pallet has reached over the limit: :palletLimitLeft", ['palletLimitLeft' => $palletLimitLeft]));
        }

        $pallet = StorePallet::make()->action($palletDelivery->fulfilmentCustomer, $modelData);

        if (Arr::exists($modelData, 'stored_item') && Arr::get($modelData, 'with_stored_item')) {
            $storedItem = StoreStoredItem::run($pallet->fulfilmentCustomer, [
                'reference' => Arr::get($modelData, 'stored_item')
            ]);

            SyncPalletStoredItem::run($pallet, ['stored_item_id' => $storedItem->id]);
        }

        AutoAssignServicesToPalletDelivery::run($palletDelivery, $pallet);
        PalletDeliveryHydratePallets::run($palletDelivery);

        return $pallet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        } elseif ($this->customer->id == $request->route()->parameter('palletDelivery')->fulfilmentCustomer->customer_id) {
            return true;
        }

        return false;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->fulfilment->warehouses()->count() == 1) {
            /** @var Warehouse $warehouse */
            $warehouse = $this->fulfilment->warehouses()->first();
            $this->fill(['warehouse_id' => $warehouse->id]);
        }
    }

    public function rules(): array
    {
        return [
            'type'               => ['nullable', Rule::enum(PalletTypeEnum::class)],
            'customer_reference' => ['nullable'],
            'notes'              => ['nullable', 'string','max:1024']
        ];
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): Pallet
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->parent       = $palletDelivery;
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request);
        return $this->handle($palletDelivery, $this->validatedData);
    }

    public function action(PalletDelivery $palletDelivery, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->action                 = true;
        $this->hydratorsDelay         = $hydratorsDelay;
        $this->parent                 = $palletDelivery;
        $fulfilmentCustomer           = $palletDelivery->fulfilmentCustomer;
        $this->actionInitialisation($fulfilmentCustomer, $modelData);

        return $this->handle($palletDelivery, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {

        return Redirect::route('retina.fulfilment.storage.pallet-deliveries.show', [
            'palletDelivery'     => $this->parent->slug
        ]);
    }
}
