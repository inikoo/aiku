<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 11:38:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Artifact;

use App\Actions\Manufacturing\Artifact\Hydrators\ArtifactHydrateUniversalSearch;
use App\Actions\Manufacturing\Production\Hydrators\ProductionHydrateArtifacts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateArtifacts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateArtifacts;
use App\Enums\Manufacturing\Artifact\ArtifactStateEnum;
use App\Models\Manufacturing\Artifact;
use App\Models\Manufacturing\Production;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreArtifact extends OrgAction
{
    public function handle(Production $production, array $modelData): Artifact
    {

        data_set($modelData, 'organisation_id', $this->organisation->id);
        data_set($modelData, 'group_id', $production->group_id);

        /** @var Artifact $artifact */
        $artifact = $production->artifacts()->create($modelData);
        $artifact->stats()->create();
        GroupHydrateArtifacts::dispatch($artifact->group);
        OrganisationHydrateArtifacts::dispatch($artifact->organisation);
        ProductionHydrateArtifacts::dispatch($artifact->production);
        ArtifactHydrateUniversalSearch::dispatch($artifact);


        return $artifact;
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
            'code'      => [
                'required',
                'max:64',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'artifacts',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'state'       => ['sometimes', 'nullable', Rule::enum(ArtifactStateEnum::class)],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'created_at'  => ['sometimes', 'nullable', 'date'],


        ];
    }

    public function action(Production $production, array $modelData, int $hydratorDelay = 0): Artifact
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($production->organisation, $modelData);

        return $this->handle($production, $this->validatedData);
    }


    public function htmlResponse(Artifact $artifact): RedirectResponse
    {
        return Redirect::route('grp.org.artifacts.show', [
            $artifact->slug
        ]);
    }

}
