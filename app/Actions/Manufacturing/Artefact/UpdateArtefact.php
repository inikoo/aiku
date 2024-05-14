<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artefact;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Manufacturing\ArtefactResource;
use App\Models\Manufacturing\Artefact;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateArtefact extends OrgAction
{
    use WithActionUpdate;


    private Artefact $artefact;

    public function handle(Artefact $artefact, array $modelData): Artefact
    {
        $stock = $this->update($artefact, $modelData, ['data', 'settings']);
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
                    table: 'artefacts',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->artefact->id
                        ],

                    ]
                ),
            ],
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
        ];
    }


    public function action(Artefact $artefact, array $modelData, int $hydratorDelay = 0): Artefact
    {
        $this->asAction       = true;
        $this->artefact       = $artefact;
        $this->hydratorsDelay = $hydratorDelay;

        $this->initialisation($artefact->organisation, $modelData);
        return $this->handle($artefact, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, Artefact $artefact, ActionRequest $request): Artefact
    {
        $this->artefact = $artefact;
        $this->initialisationFromProduction($production, $request);

        return $this->handle($artefact, $this->validatedData);
    }


    public function jsonResponse(Artefact $artefact): ArtefactResource
    {
        return new ArtefactResource($artefact);
    }
}
