<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePallets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFulfilmentCustomers;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePallet extends OrgAction
{
    private FulfilmentCustomer $fulfilmentCustomer;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): Pallet
    {
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment->id);

        /** @var Pallet $pallet */
        $pallet = $fulfilmentCustomer->pallets()->create($modelData);
        FulfilmentCustomerHydratePallets::dispatch($fulfilmentCustomer);
        OrganisationHydrateFulfilmentCustomers::dispatch($fulfilmentCustomer->organisation);

        $pallet->refresh();
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
            'notes'              => ['sometimes', 'string'],
            'created_at'         => ['sometimes', 'date'],
            'received_at'        => ['sometimes', 'nullable', 'date'],
            'source_id'          => ['sometimes', 'nullable', 'string'],
            'warehouse_id'       => ['required', 'integer', 'exists:warehouses,id'],
            'location_id'        => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
        ];
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Pallet
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->asAction                 = true;
        $this->hydratorsDelay           = $hydratorsDelay;
        $this->fulfilmentCustomer       = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.pallets.show', $pallet->slug);
    }
}
