<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePallets;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\Fulfilment\Pallet\Hydrators\PalletHydrateUniversalSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePallets;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydratePallets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePallets;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
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

class StorePallet extends OrgAction
{
    private FulfilmentCustomer $fulfilmentCustomer;

    private PalletDelivery|FulfilmentCustomer $parent;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): Pallet
    {
        if (Arr::get($modelData, 'notes') === null) {
            data_set($modelData, 'notes', '');
        }

        if (Arr::exists($modelData, 'state') and Arr::get($modelData, 'state') != PalletStateEnum::IN_PROCESS) {
            if (!Arr::get($modelData, 'reference')) {
                data_set(
                    $modelData,
                    'reference',
                    GetSerialReference::run(
                        container: $fulfilmentCustomer,
                        modelType: SerialReferenceModelEnum::PALLET
                    )
                );
            }
        }

        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment->id);

        /** @var Pallet $pallet */
        $pallet = $fulfilmentCustomer->pallets()->create($modelData);

        if ($pallet->reference) {
            $pallet->generateSlug();
            $pallet->save();
        }
        $pallet->refresh();


        if ($this->parent instanceof PalletDelivery) {
        }
        FulfilmentCustomerHydratePallets::dispatch($fulfilmentCustomer);
        FulfilmentHydratePallets::dispatch($fulfilmentCustomer->fulfilment);
        OrganisationHydratePallets::dispatch($fulfilmentCustomer->organisation);
        WarehouseHydratePallets::dispatch($pallet->warehouse);
        if ($pallet->location && $pallet->location->warehouseArea) {
            WarehouseAreaHydratePallets::dispatch($pallet->location->warehouseArea);
        }
        PalletHydrateUniversalSearch::dispatch($pallet);

        return $pallet;
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


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): Pallet
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

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->parent             = $fulfilmentCustomer;
        $this->asAction           = true;
        $this->hydratorsDelay     = $hydratorsDelay;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        if ($this->parent instanceof PalletDelivery) {
            return Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show', [
                'organisation'       => $pallet->organisation->slug,
                'fulfilment'         => $pallet->fulfilment->slug,
                'fulfilmentCustomer' => $pallet->fulfilmentCustomer->slug,
                'palletDelivery'     => $this->parent->slug
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
