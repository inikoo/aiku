<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 15:33:20 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Production;

use App\Actions\Manufacturing\Production\Hydrators\ProductionHydrateUniversalSearch;
use App\Actions\OrgAction;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProductions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProductions;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Manufacturing\Production\ProductionStateEnum;
use App\Http\Resources\Manufacturing\ProductionResource;
use App\Models\Manufacturing\Production;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProduction extends OrgAction
{
    use WithActionUpdate;



    private Production $production;

    public function handle(Production $production, array $modelData): Production
    {
        $production = $this->update($production, $modelData, ['data', 'settings']);
        if ($production->wasChanged('state')) {
            GroupHydrateProductions::run($production->group);
            OrganisationHydrateProductions::dispatch($production->organisation);
        }
        ProductionHydrateUniversalSearch::dispatch($production);

        return $production;
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
            'code'               => [
                'sometimes',
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'productions',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                        ['column' => 'id', 'value' => $this->production->id, 'operation' => '!=']
                    ]
                ),
            ],
            'name'               => ['sometimes', 'required', 'max:250', 'string'],
            'state'              => ['sometimes', Rule::enum(ProductionStateEnum::class)],
            'allow_stock'        => ['sometimes', 'required', 'boolean'],
            'allow_fulfilment'   => ['sometimes', 'required', 'boolean'],
            'allow_dropshipping' => ['sometimes', 'required', 'boolean']
        ];
    }


    public function asController(Production $production, ActionRequest $request): Production
    {
        $this->production = $production;
        $this->initialisation($production->organisation, $request);


        return $this->handle(
            production: $production,
            modelData: $this->validatedData
        );
    }

    public function action(Production $production, $modelData): Production
    {
        $this->asAction   = true;
        $this->production = $production;
        $this->initialisation($production->organisation, $modelData);


        return $this->handle(
            production: $production,
            modelData: $this->validatedData
        );
    }

    public function jsonResponse(Production $production): ProductionResource
    {
        return new ProductionResource($production);
    }
}
