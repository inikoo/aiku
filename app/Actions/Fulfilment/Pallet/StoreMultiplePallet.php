<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreMultiplePallet extends OrgAction
{
    private FulfilmentCustomer $fulfilmentCustomer;
    /**
     * @var \App\Models\Fulfilment\FulfilmentCustomer|\App\Models\Fulfilment\PalletDelivery
     */
    private PalletDelivery|FulfilmentCustomer $parent;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): void
    {
        for ($i = 1; $i < Arr::get($modelData, 'number_pallets'); $i++) {
            StorePallet::run($fulfilmentCustomer, $modelData);
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
            'customer_reference' => [
                'sometimes',
                'nullable',
                'max:64',
                'string',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'pallets',
                    extraConditions: [
                        ['column' => 'fulfilment_customer_id', 'value' => $this->fulfilmentCustomer->id],
                    ]
                ),


            ],
            'state'              => [
                'sometimes',
                Rule::enum(PalletStateEnum::class)
            ],
            'status'             => [
                'sometimes',
                Rule::enum(PalletStatusEnum::class)
            ],
            'type'               => [
                'sometimes',
                Rule::enum(PalletTypeEnum::class)
            ],
            'notes'              => ['sometimes', 'nullable', 'string', 'max:1024'],
            'created_at'         => ['sometimes', 'date'],
            'received_at'        => ['sometimes', 'nullable', 'date'],
            'source_id'          => ['sometimes', 'nullable', 'string'],
            'warehouse_id'       => ['required', 'integer', 'exists:warehouses,id'],
            'location_id'        => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
            'pallet_delivery_id' => ['sometimes', 'nullable', 'integer', 'exists:pallet_deliveries,id']
        ];
    }


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->parent             = $palletDelivery;
        $this->fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
        $request->merge(
            [
                'pallet_delivery_id' => $palletDelivery->id,
                'warehouse_id'       => $palletDelivery->warehouse_id
            ]
        );

        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function inCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): void
    {
        $this->parent             = $fulfilmentCustomer;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData, int $hydratorsDelay = 0): void
    {
        $this->asAction           = true;
        $this->hydratorsDelay     = $hydratorsDelay;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        if($this->parent instanceof PalletDelivery) {
            return Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show', [
                'organisation'       => $pallet->organisation->slug,
                'fulfilment'         => $pallet->fulfilment->slug,
                'fulfilmentCustomer' => $pallet->fulfilmentCustomer->slug,
                'palletDelivery'     => $this->parent->reference
            ]);
        }

        return Redirect::route(
            'grp.org.fulfilments.show.crm.customers.show',
            [
                'organisation'       => $pallet->organisation->slug,
                'fulfilment'         => $pallet->fulfilment->slug,
                'fulfilmentCustomer' => $pallet->fulfilmentCustomer->slug,
            ]
        );
    }
}
