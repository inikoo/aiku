<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreMultiplePallets extends OrgAction
{
    private FulfilmentCustomer $fulfilmentCustomer;

    private PalletDelivery|FulfilmentCustomer $parent;

    public function handle(PalletDelivery $palletDelivery, array $modelData): void
    {
        for ($i = 1; $i <= Arr::get($modelData, 'number_pallets'); $i++) {
            StorePalletFromDelivery::run($palletDelivery, Arr::except($modelData, 'number_pallets'));
        }
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }


    public function rules(): array
    {
        return [


            'warehouse_id'       => ['required', 'integer', 'exists:warehouses,id'],
            'number_pallets'     => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->parent             = $palletDelivery;
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $request->merge(
            [
                'warehouse_id'       => $palletDelivery->warehouse_id
            ]
        );

        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function action(PalletDelivery $palletDelivery, array $modelData, int $hydratorsDelay = 0): void
    {
        $this->asAction           = true;
        $this->hydratorsDelay     = $hydratorsDelay;
        $this->parent             = $palletDelivery;
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $this->initialisationFromFulfilment($palletDelivery->fulfilmentCustomer->fulfilment, $modelData);

        $this->handle($palletDelivery, $this->validatedData);
    }


    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show', [
            'organisation'       => $this->organisation->slug,
            'fulfilment'         => $this->fulfilment->slug,
            'fulfilmentCustomer' => $this->fulfilmentCustomer->slug,
            'palletDelivery'     => $this->parent->reference
        ]);
    }
}
