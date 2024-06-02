<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\HistoricAsset\StoreHistoricAsset;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateHistoricAssets;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateOuters;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Asset;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;

class StoreOuterTODELETE extends OrgAction
{
    public function handle(Asset $product, array $modelData, bool $skipHistoric = false): Product
    {

        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'state', match ($product->state) {
            AssetStateEnum::IN_PROCESS     => ProductStateEnum::IN_PROCESS,
            AssetStateEnum::ACTIVE         => ProductStateEnum::ACTIVE,
            AssetStateEnum::DISCONTINUING  => ProductStateEnum::DISCONTINUING,
            AssetStateEnum::DISCONTINUED   => ProductStateEnum::DISCONTINUED,
        });
        data_set($modelData, 'price', $product->price);

        /** @var Product $outer */
        $outer = $product->outers()->create($modelData);
        $outer->salesIntervals()->create();


        if (!$skipHistoric) {
            $historicProduct = StoreHistoricAsset::run($outer, [
                'source_id'=> $outer->historic_source_id
            ]);
            $product->update(
                [
                    'current_historic_asset_id' => $historicProduct->id
                ]
            );
        }

        //AssetHydrateOuters::dispatch($product);
        //AssetHydrateHistoricAssets::dispatch($product);
        //ProductHydrateUniversalSearch::dispatch($outer);

        return $outer;
    }

    public function rules(): array
    {
        return [
            'is_main'     => ['required', 'boolean'],
            'code'        => [
                'required',
                'max:32',
                new AlphaDashDot(),
                new IUnique(
                    table: 'outers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                    ]
                ),
            ],
            'units'       => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],

            'price'                => ['required', 'numeric'],
            'source_id'            => ['sometimes', 'required', 'string', 'max:255'],
            'historic_source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'state'                => ['required', Rule::enum(ProductStateEnum::class)],
            'data'                 => ['sometimes', 'array'],
            'created_at'           => ['sometimes', 'date'],
        ];

    }

    public function action(Asset $product, array $modelData, int $hydratorsDelay = 0): Product
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }




}
