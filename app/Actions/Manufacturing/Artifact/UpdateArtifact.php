<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artifact;

use App\Actions\GrpAction;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\OrgStockResource;
use App\Models\Manufacturing\Artifact;
use App\Models\SupplyChain\StockFamily;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateArtifact extends GrpAction
{
    use WithActionUpdate;

    private StockFamily $stockFamily;

    private Artifact $artifact;

    public function handle(Artifact $artifact, array $modelData): Artifact
    {
        $stock = $this->update($artifact, $modelData, ['data', 'settings']);
        OrgStockHydrateUniversalSearch::dispatch($stock);

        return $stock;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.stocks.edit");
    }

    public function rules(): array
    {
        return [
            'code'            => [
                'sometimes',
                'required',
                new AlphaDashDot(),
                'max:32',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'stocks',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->stock->id
                        ],

                    ]
                ),
            ],
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
        ];
    }


    public function action(Artifact $artifact, array $modelData): Artifact
    {
        $this->asAction = true;
        $this->stock    = $artifact;
        $this->initialisation($artifact->group, $modelData);

        return $this->handle($artifact, $this->validatedData);
    }

    public function asController(Artifact $artifact, ActionRequest $request): Artifact
    {
        $this->artifact = $artifact;
        $this->initialisation($artifact->group, $request);

        return $this->handle($artifact, $this->validatedData);
    }


    public function jsonResponse(Artifact $artifact): OrgStockResource
    {
        return new OrgStockResource($artifact);
    }
}
