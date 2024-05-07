<?php

namespace App\Actions\Manufacturing\RawMaterial;

use App\Actions\Manufacturing\RawMaterial\Hydrators\RawMaterialHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRawMaterials;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRawMaterials;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\RawMaterial;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRawMaterial extends OrgAction
{
    use WithActionUpdate;

    /**
     * @var \App\Models\Manufacturing\RawMaterial
     */
    private RawMaterial $rawMaterial;

    public function handle(RawMaterial $rawMaterial, array $modelData): RawMaterial
    {
        $rawMaterial= $this->update($rawMaterial, $modelData);
        if($rawMaterial->wasChanged('state')) {
            GroupHydrateRawMaterials::dispatch($rawMaterial->group);
            OrganisationHydrateRawMaterials::dispatch($rawMaterial->organisation);
        }
        RawMaterialHydrateUniversalSearch::dispatch($rawMaterial);
        return $rawMaterial;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.productions.edit");
    }


    public function rules(): array
    {
        return [
            'type'                          => ['sometimes',Rule::enum(RawMaterialTypeEnum::class)],
            'state'                         => ['sometimes',Rule::enum(RawMaterialStateEnum::class)],
            'code'                          => [
                'sometimes',
                'alpha_dash',
                'max:64',
                new IUnique(
                    table: 'raw_materials',
                    extraConditions: [
                        [
                            'column' => 'organisation_id', 'value' => $this->organisation->id,
                        ],
                        [
                            'column' => 'id', 'value' => $this->rawMaterial->id, 'operation' => '!='
                        ]

                    ]
                ),
            ],
            'description'                   => ['sometimes','string', 'max:255'],
            'unit'                          => ['sometimes',Rule::enum(RawMaterialUnitEnum::class)],
            'unit_cost'                     => ['sometimes','numeric', 'min:0'],
        ];
    }

    public function asController(RawMaterial $rawMaterial, ActionRequest $request): RawMaterial
    {
        $this->rawMaterial = $rawMaterial;
        $this->initialisation($rawMaterial->organisation, $request);


        return $this->handle(
            rawMaterial: $rawMaterial,
            modelData: $this->validatedData
        );
    }

    public function action(RawMaterial $rawMaterial, $modelData): RawMaterial
    {
        $this->asAction    = true;
        $this->rawMaterial = $rawMaterial;
        $this->initialisation($rawMaterial->organisation, $modelData);


        return $this->handle(
            rawMaterial: $rawMaterial,
            modelData: $this->validatedData
        );
    }
}
