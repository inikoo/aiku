<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:25:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\Warehouse\Search\WarehouseRecordSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateWarehouses;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouses;
use App\Actions\SysAdmin\Organisation\SeedJobPositions;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Actions\Traits\WithModelAddressActions;
use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreWarehouse extends OrgAction
{
    use WithModelAddressActions;

    public function handle(Organisation $organisation, $modelData): Warehouse
    {
        data_set($modelData, 'group_id', $organisation->group_id);

        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        /** @var Warehouse $warehouse */
        $warehouse = $organisation->warehouses()->create($modelData);
        $warehouse->stats()->create();


        if (Arr::get($warehouse->settings, 'address_link')) {
            $warehouse = $this->addLinkedAddress($warehouse);
        } else {
            $warehouse = $this->addDirectAddress($warehouse, $addressData);
        }


        SeedWarehousePermissions::run($warehouse);

        $orgAdmins = $organisation->group->users()->with('roles')->get()->filter(
            fn ($user) => $user->roles->where('name', "org-admin-$organisation->id")->toArray()
        );
        foreach ($orgAdmins as $orgAdmin) {
            UserAddRoles::run($orgAdmin, [
                Role::where('name', RolesEnum::getRoleName(RolesEnum::WAREHOUSE_ADMIN->value, $warehouse))->first()
            ]);
        }

        GroupHydrateWarehouses::run($organisation->group);
        OrganisationHydrateWarehouses::run($organisation);
        WarehouseRecordSearch::dispatch($warehouse);
        SeedJobPositions::run($organisation);


        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code'       => [
                'required',
                'between:2,4',
                'alpha_dash',
                new IUnique(
                    table: 'warehouses',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'name'             => ['required', 'max:250', 'string'],
            'state'            => ['sometimes', Rule::enum(WarehouseStateEnum::class)],
            'source_id'        => ['sometimes', 'string'],
            'created_at'       => ['sometimes', 'date'],
            'address'          => ['sometimes', 'required', new ValidAddress()],
            'settings'         => ['sometimes', 'array'],
            'fetched_at'       => ['sometimes', 'date'],

        ];
    }

    public function action(Organisation $organisation, array $modelData): Warehouse
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }


    public function asController(Organisation $organisation, ActionRequest $request): Warehouse
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }


    public function htmlResponse(Warehouse $warehouse): RedirectResponse
    {
        return Redirect::route('grp.org.warehouses.index');
    }

    public string $commandSignature = 'warehouse:create {organisation : organisation slug} {code} {name}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        $this->organisation = $organisation;
        setPermissionsTeamId($organisation->group->id);

        $this->setRawAttributes([
            'code' => $command->argument('code'),
            'name' => $command->argument('name'),
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $warehouse = $this->handle($organisation, $validatedData);

        $command->info("Warehouse $warehouse->code created successfully 🎉");

        return 0;
    }

}
