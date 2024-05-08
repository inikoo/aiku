<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Manufacturing\Artifact;

use App\Actions\Goods\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\Goods\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInventory;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\Manufacturing\Artifact;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreArtifact extends GrpAction
{
    public function handle(Group $group, $modelData): Artifact
    {
        /** @var Artifact $artifact */
        $artifact = $group->stocks()->create($modelData);
        $artifact->stats()->create();
        GroupHydrateInventory::dispatch($group);
        if ($artifact->stock_family_id) {
            StockFamilyHydrateStocks::dispatch($artifact->stockFamily)->delay($this->hydratorsDelay);
        }
        StockHydrateUniversalSearch::dispatch($artifact);


        return $artifact;
    }

    public function rules(): array
    {
        return [
            'code'            => [
                'required',
                'max:64',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'stocks',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),
            ],
            'stock_id'        => ['required', 'exists:stocks,id'],
            'name'            => ['required', 'string', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
            'source_id'       => ['sometimes', 'nullable', 'string'],
            'source_slug'     => ['sometimes', 'nullable', 'string'],
            'state'           => ['sometimes', 'nullable', Rule::enum(StockStateEnum::class)],
        ];
    }

    public function action(Group $group, array $modelData, int $hydratorDelay = 0): Artifact
    {
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Artifact
    {
        $this->fill(
            [
                'stock_family_id' => $stockFamily->id
            ]
        );
        $this->initialisation(group(), $request);


        return $this->handle(group(), $this->validatedData);
    }

    public function htmlResponse(Artifact $artifact): RedirectResponse
    {
        if (!$artifact->stock_family_id) {
            return Redirect::route('grp.org.inventory.org-stock-families.show.stocks.show', [
                $artifact->stockFamily->slug,
                $artifact->slug
            ]);
        } else {
            return Redirect::route('grp.org.inventory.org-stocks.show', [
                $artifact->slug
            ]);
        }
    }
}
