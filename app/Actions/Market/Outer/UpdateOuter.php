<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 15:22:28 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Outer;

use App\Actions\Market\Outer\Hydrator\OuterHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Market\OuterResource;
use App\Models\Market\Outer;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateOuter extends OrgAction
{
    use WithActionUpdate;

    private Outer $outer;

    public function handle(Outer $outer, array $modelData, bool $skipHistoric = false): Outer
    {
        $outer = $this->update($outer, $modelData, ['data', 'settings']);
        if (!$skipHistoric and $outer->wasChanged(
            ['price', 'code', 'name', 'units']
        )) {
            //todo create HistoricOuter and update current_historic_outer_id if
        }
        OuterHydrateUniversalSearch::dispatch($outer);

        return $outer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.Outers.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'Outers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'value' => null],
                        ['column' => 'id', 'value' => $this->outer->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function asController(Outer $outer, ActionRequest $request): Outer
    {
        $this->outer = $outer;
        $this->initialisationFromShop($outer->shop, $request);

        return $this->handle($outer, $this->validatedData);
    }

    public function action(Outer $outer, array $modelData, int $hydratorsDelay = 0, $skipHistoric = false): Outer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->outer          = $outer;

        $this->initialisationFromShop($outer->shop, $modelData);

        return $this->handle($outer, $this->validatedData, $skipHistoric);
    }

    public function jsonResponse(Outer $outer): OuterResource
    {
        return new OuterResource($outer);
    }
}
