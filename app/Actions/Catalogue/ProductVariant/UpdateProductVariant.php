<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:31:38 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductVariant;

use App\Actions\Catalogue\HistoricProductVariant\StoreHistoricProductVariant;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Models\Catalogue\ProductVariant;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProductVariant extends OrgAction
{
    use WithActionUpdate;


    private ProductVariant $productVariant;

    public function handle(ProductVariant $productVariant, array $modelData): ProductVariant
    {

        $productVariant  = $this->update($productVariant, $modelData);
        $changed         = $productVariant->getChanges();

        if (Arr::hasAny($changed, ['name', 'code', 'price','units','unit'])) {
            $historicProductVariant = StoreHistoricProductVariant::run($productVariant);
            $productVariant->updateQuietly(
                [
                    'current_historic_product_variant_id' => $historicProductVariant->id,
                ]
            );
        }




        return $productVariant;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.products.edit");
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
                    table: 'product_variants',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->productVariant->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'max:250', 'string'],
            'price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['sometimes', 'required', 'max:1500'],
            'rrp'         => ['sometimes', 'required', 'numeric'],
            'data'        => ['sometimes', 'array'],
            'settings'    => ['sometimes', 'array'],
            'status'      => ['sometimes', 'required', 'boolean'],
            'state'       => ['sometimes', 'required', Rule::enum(AssetStateEnum::class)],
        ];
    }

    public function asController(ProductVariant $productVariant, ActionRequest $request): ProductVariant
    {
        $this->productVariant = $productVariant;
        $this->initialisationFromShop($productVariant->shop, $request);

        return $this->handle($productVariant, $this->validatedData);
    }

    public function action(ProductVariant $productVariant, array $modelData, int $hydratorsDelay = 0): ProductVariant
    {
        $this->asAction              = true;
        $this->hydratorsDelay        = $hydratorsDelay;
        $this->productVariant        = $productVariant;

        $this->initialisationFromShop($productVariant->shop, $modelData);

        return $this->handle($productVariant, $this->validatedData);
    }


}
