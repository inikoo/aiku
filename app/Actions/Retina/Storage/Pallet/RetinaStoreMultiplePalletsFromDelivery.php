<?php
/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-34m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\Pallet;

use App\Actions\InertiaAction;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class RetinaStoreMultiplePalletsFromDelivery extends RetinaAction
{
    private FulfilmentCustomer $fulfilmentCustomer;

    private PalletDelivery|FulfilmentCustomer $parent;

    public function handle(PalletDelivery $palletDelivery, array $modelData): void
    {
        data_set($modelData, 'warehouse_id', $palletDelivery->warehouse_id);

        for ($i = 1; $i <= Arr::get($modelData, 'number_pallets'); $i++) {
            RetinaStorePalletFromDelivery::run($palletDelivery, Arr::except($modelData, ['number_pallets']));
        }
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
            'warehouse_id'   => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('organisation_id', $this->organisation->id),
            ],
            'number_pallets' => ['required', 'integer', 'min:1', 'max:1000'],
            'type'           => ['required', Rule::enum(PalletTypeEnum::class)],
        ];
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;
        $this->parent       = $palletDelivery;

        $this->initialisation($request);
        $this->handle($palletDelivery, $this->validatedData);
    }

    public function action(PalletDelivery $palletDelivery, array $modelData, int $hydratorsDelay = 0): void
    {
        $this->asAction           = true;
        $this->hydratorsDelay     = $hydratorsDelay;
        $this->parent             = $palletDelivery;
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $this->initialisation($modelData);

        $this->handle($palletDelivery, $this->validatedData);
    }


    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('retina.fulfilment.storage.pallet-deliveries.show', [
            'palletDelivery' => $this->parent->slug
        ]);
    }
}
