<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Outer;

use App\Actions\Market\HistoricOuter\StoreHistoricOuter;
use App\Actions\Market\Outer\Hydrator\OuterHydrateUniversalSearch;
use App\Actions\Market\Product\Hydrators\ProductHydrateOuters;
use App\Actions\OrgAction;
use App\Enums\Market\Outer\OuterStateEnum;
use App\Models\Market\Outer;
use App\Models\Market\Product;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;

class StoreOuter extends OrgAction
{
    private bool |null $state=null;

    public function handle(Product $product, array $modelData, bool $skipHistoric = false): Outer
    {


        data_set($modelData, 'organisation_id', $product->organisation_id);
        data_set($modelData, 'group_id', $product->group_id);
        data_set($modelData, 'shop_id', $product->shop_id);
        data_set($modelData, 'product_id', $product->product_id);

        /** @var Outer $outer */
        $outer = $product->outers()->create($modelData);
        $outer->salesStats()->create();


        if (!$skipHistoric) {
            $historicProduct = StoreHistoricOuter::run($outer);
            $product->update(
                [
                    'current_historic_outer_id' => $historicProduct->id
                ]
            );
        }

        ProductHydrateOuters::dispatch($outer->product);
        OuterHydrateUniversalSearch::dispatch($outer);

        return $outer;
    }

    public function rules(): array
    {
        return [
            'is_main'     => ['required', 'boolean'],
            'code'        => [
                'required',
                'max:32',
                'alpha_dash',
                new IUnique(
                    table: 'outers',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),
            ],
            'units'       => ['sometimes', 'required', 'numeric'],
            'name'        => ['required', 'max:250', 'string'],

            'price'       => ['required', 'numeric'],
            'source_id'   => ['sometimes', 'required', 'string', 'max:255'],
            'state'       => ['required', Rule::enum(OuterStateEnum::class)],
            'data'        => ['sometimes', 'array'],
            'created_at'  => ['sometimes', 'date'],
        ];

    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Outer
    {
        $this->hydratorsDelay = $hydratorsDelay;
        $this->asAction       = true;


        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }




}
