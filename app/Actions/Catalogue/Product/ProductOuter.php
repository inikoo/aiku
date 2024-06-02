<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 15:22:28 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\Asset\SetProductMainOuter;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Catalogue\OuterResource;
use App\Models\Catalogue\Product;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class ProductOuter extends OrgAction
{
    use WithActionUpdate;

    private Product $outer;

    public function handle(Product $outer, array $modelData): Product
    {

        $outer  = $this->update($outer, $modelData);
        $changed=$outer->getChanges();


        if(Arr::hasAny($changed, ['name', 'code', 'price'])) {

            $historicOuterable=StoreHistoricAsset::run($outer);

            if($outer->is_main) {
                SetProductMainOuter::run($outer->product, $outer);
                $outer->product->update(
                    [
                        'current_historic_asset_id' => $historicOuterable->id,
                    ]
                );
            }


        }


        ProductHydrateUniversalSearch::dispatch($outer);

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

    public function asController(Product $outer, ActionRequest $request): Product
    {
        $this->outer = $outer;
        $this->initialisationFromShop($outer->shop, $request);

        return $this->handle($outer, $this->validatedData);
    }

    public function action(Product $outer, array $modelData, int $hydratorsDelay = 0): Product
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->outer          = $outer;

        $this->initialisationFromShop($outer->shop, $modelData);

        return $this->handle($outer, $this->validatedData);
    }

    public function jsonResponse(Product $outer): OuterResource
    {
        return new OuterResource($outer);
    }
}
