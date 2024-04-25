<?php

namespace App\Actions\Market\Product;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\Outer\UpdateOuter;
use App\Actions\Market\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\Market\Rental\UpdateRental;
use App\Actions\Market\Service\UpdateService;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Http\Resources\Market\ProductResource;
use App\Models\Market\Product;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateServicex extends OrgAction
{
    use WithActionUpdate;

    private Product $product;

    public function handle(Product $product, array $modelData): Product
    {


        if($product->type==ProductTypeEnum::PHYSICAL_GOOD) {
            $mainOuterData= Arr::only($modelData, ['name','code','price']);
            UpdateOuter::run($product->mainOuterable, $mainOuterData);
            $modelData=Arr::except($modelData, ['name','code','price']);
        } else {

            switch ($product->type) {
                case ProductTypeEnum::SERVICE:
                    UpdateService::run($product->mainOuterable, Arr::only($modelData, ['state','status']));
                    break;
                case ProductTypeEnum::RENTAL:
                    UpdateRental::run($product->mainOuterable, Arr::only($modelData, ['state','status']));
                    break;
            }


            if (Arr::has($modelData, 'price')) {

                $price = Arr::get($modelData, 'price');
                data_forget($modelData, 'price');
                data_set($modelData, 'main_outerable_price', $price);
            }

        }





        $product = $this->update($product, $modelData, ['data', 'settings']);
        $product->refresh();

        if($product->type!=ProductTypeEnum::PHYSICAL_GOOD) {
            $changed=$product->getChanges();

            if(Arr::hasAny($changed, ['name', 'code', 'price'])) {
                //  dd($product);
                $historicOuterable = StoreHistoricOuterable::run($product->mainOuterable);
                $product->update(
                    [
                        'current_historic_outerable_id' => $historicOuterable->id,
                    ]
                );
            }
        }

        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
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
                    table: 'products',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'deleted_at', 'operator'=>'notNull'],
                        ['column' => 'id', 'value' => $this->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'                       => ['sometimes', 'required', 'max:250', 'string'],
            'main_outerable_price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'                => ['sometimes', 'required', 'max:1500'],
            'rrp'                        => ['sometimes', 'required', 'numeric'],
            'data'                       => ['sometimes', 'array'],
            'settings'                   => ['sometimes', 'array'],
            'status'                     => ['sometimes', 'required', 'boolean'],
            'state'                      => ['sometimes', 'required', Rule::enum(ProductStateEnum::class)],
        ];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $this->validatedData);
    }

    public function action(Product $product, array $modelData, int $hydratorsDelay = 0): Product
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->product        = $product;

        $this->initialisationFromShop($product->shop, $modelData);

        return $this->handle($product, $this->validatedData);
    }

    public function jsonResponse(Product $product): ProductResource
    {
        return new ProductResource($product);
    }
}
