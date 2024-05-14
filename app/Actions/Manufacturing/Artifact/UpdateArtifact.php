<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artifact;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Manufacturing\ArtifactResource;
use App\Models\Manufacturing\Artifact;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateArtifact extends OrgAction
{
    use WithActionUpdate;


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

        return $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.edit");
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
                    table: 'artifacts',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->artifact->id
                        ],

                    ]
                ),
            ],
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
        ];
    }


    public function action(Artifact $artifact, array $modelData, int $hydratorDelay = 0): Artifact
    {
        $this->asAction       = true;
        $this->artifact       = $artifact;
        $this->hydratorsDelay = $hydratorDelay;

        $this->initialisation($artifact->organisation, $modelData);
        return $this->handle($artifact, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, Artifact $artifact, ActionRequest $request): Artifact
    {
        $this->artifact = $artifact;
        $this->initialisationFromProduction($production, $request);

        return $this->handle($artifact, $this->validatedData);
    }


    public function jsonResponse(Artifact $artifact): ArtifactResource
    {
        return new ArtifactResource($artifact);
    }
}
