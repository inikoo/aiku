<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateStoredItems;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFulfilment;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePallet extends OrgAction
{
    private Customer $customer;

    public function handle(Customer $customer, array $modelData): Pallet
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'fulfilment_id', $customer->shop->fulfilment->id);

        /** @var Pallet $pallet */
        $pallet = $customer->pallets()->create($modelData);
        //CustomerHydrateStoredItems::dispatch($customer);
        // OrganisationHydrateFulfilment::dispatch();

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
                        ['column' => 'customer_id', 'value' => $this->customer->id],
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
        ];
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Customer $customer, ActionRequest $request): Pallet
    {
        $this->customer = $customer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($customer, $this->validateAttributes());
    }

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->customer       = $customer;
        $this->initialisationFromFulfilment($customer->shop->fulfilment, $modelData);

        return $this->handle($customer, $this->validatedData);
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilment.pallets.show', $pallet->slug);
    }
}
