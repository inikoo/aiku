<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 15:22:28 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Outer;

use App\Actions\Catalogue\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Catalogue\Outer\Hydrators\OuterHydrateUniversalSearch;
use App\Actions\Catalogue\Billable\SetProductMainOuter;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\OuterResource;
use App\Models\Catalogue\Outer;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateOuter extends OrgAction
{
    use WithActionUpdate;

    private Outer $outer;

    public function handle(Outer $outer, array $modelData): Outer
    {

        $outer  = $this->update($outer, $modelData);
        $changed=$outer->getChanges();


        if(Arr::hasAny($changed, ['name', 'code', 'price'])) {

            $historicOuterable=StoreHistoricOuterable::run($outer);

            if($outer->is_main) {
                SetProductMainOuter::run($outer->product, $outer);
                $outer->product->update(
                    [
                        'current_historic_outerable_id' => $historicOuterable->id,
                    ]
                );
            }


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
                new AlphaDashDot(),
                new IUnique(
                    table: 'outers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->outer->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],

        ];
    }

    public function asController(Outer $outer, ActionRequest $request): Outer
    {
        $this->outer = $outer;
        $this->initialisationFromShop($outer->shop, $request);

        return $this->handle($outer, $this->validatedData);
    }

    public function action(Outer $outer, array $modelData, int $hydratorsDelay = 0): Outer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->outer          = $outer;

        $this->initialisationFromShop($outer->shop, $modelData);

        return $this->handle($outer, $this->validatedData);
    }

    public function jsonResponse(Outer $outer): OuterResource
    {
        return new OuterResource($outer);
    }
}
