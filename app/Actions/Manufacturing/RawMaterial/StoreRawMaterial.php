<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 21:45:50 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRawMaterials;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRawMaterials;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreRawMaterial extends OrgAction
{
    use AsAction;

    public function handle(Production $production, $modelData): RawMaterial
    {
        data_set($modelData, 'group_id', $production->group_id);
        data_set($modelData, 'organisation_id', $production->organisation_id);

        /** @var RawMaterial $rawMaterial */
        $rawMaterial = $production->rawMaterials()->create($modelData);
        GroupHydrateRawMaterials::dispatch($production->group);
        OrganisationHydrateRawMaterials::dispatch($production->organisation);
        return $rawMaterial;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        //todo create/find correct permissions
        return $request->user()->hasPermissionTo("inventory.productions.edit");
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', Rule::enum(RawMaterialTypeEnum::class)],
            'state'            => ['required', Rule::enum(RawMaterialStateEnum::class)],
            'code'             => [
                'required',
                'alpha_dash',
                'max:64',
                new IUnique(
                    table: 'raw_materials',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),
            ],
            'description'      => ['required', 'string', 'max:255'],
            'unit'             => ['required', Rule::enum(RawMaterialUnitEnum::class)],
            'unit_cost'        => ['required', 'numeric', 'min:0'],
        ];
    }

    public function action(Production $production, array $modelData): RawMaterial
    {
        $this->asAction = true;
        $this->initialisation($production->organisation, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): RawMaterial
    {
        $this->initialisation($organisation, $request);

        return $this->handle($production, $this->validatedData);
    }
}
