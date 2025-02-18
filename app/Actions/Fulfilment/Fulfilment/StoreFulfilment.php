<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 11:06:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Actions\Traits\Rules\WithStoreShopRules;
use App\Actions\Utils\Abbreviate;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Role;
use Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreFulfilment extends OrgAction
{
    use WithStoreShopRules;

    public function handle(Shop $shop, array $modelData): Fulfilment
    {
        data_set($modelData, 'group_id', $shop->group_id);
        data_set($modelData, 'organisation_id', $shop->organisation_id);

        data_set($modelData, 'settings', [

            'rental_agreement_cut_off' => [
                RentalAgreementBillingCycleEnum::WEEKLY->value => [
                    'type' => RentalAgreementBillingCycleEnum::WEEKLY->value,
                    'day' => 'Friday'
                ],
                RentalAgreementBillingCycleEnum::MONTHLY->value => [
                    'type'    => RentalAgreementBillingCycleEnum::MONTHLY->value,
                    'day'     => 20,
                    'is_weekdays' => true
                ]
            ]

        ]);

        $warehouses = $modelData['warehouses'];
        Arr::forget($modelData, 'warehouses');

        /** @var Fulfilment $fulfilment */
        $fulfilment = $shop->fulfilment()->create($modelData);
        $fulfilment->stats()->create();

        SeedFulfilmentPermissions::run($fulfilment);
        SeedFulfilmentOutboxes::run($fulfilment);

        $orgAdmins = $fulfilment->group->users()->with('roles')->get()->filter(
            fn ($user) => $user->roles->where('name', "org-admin-{$fulfilment->organisation->id}")->toArray()
        );

        foreach ($orgAdmins as $orgAdmin) {
            UserAddRoles::run($orgAdmin, [
                Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_SHOP_SUPERVISOR->value, $fulfilment))->first()
            ]);
            UserAddRoles::run($orgAdmin, [
                Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR->value, $fulfilment))->first()
            ]);
        }

        foreach ($warehouses as $warehouseID) {
            $warehouse = Warehouse::find($warehouseID);
            AttachWarehouseToFulfilment::run($fulfilment, $warehouse);
        }

        $fulfilment->serialReferences()->create(
            [
                'model'           => SerialReferenceModelEnum::RENTAL_AGREEMENT,
                'organisation_id' => $fulfilment->organisation->id,
                'format'          => Abbreviate::run($fulfilment->slug).'-ra%03d'
            ]
        );

        return $fulfilment;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo('org-supervisor.'.$this->organisation->id);
    }

    public function rules(ActionRequest $request): array
    {
        return [

            'warehouses'               => ['sometimes', 'array'],
            'warehouses.*'             => [Rule::Exists('warehouses', 'id')->where('organisation_id', $this->organisation->id)],

        ];
    }


    public function action(Shop $shop, $modelData, $hydratorsDelay = 0): Fulfilment
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($shop->organisation, $modelData);

        return $this->handle($shop, $this->validatedData);
    }



}
