<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 17 Apr 2024 14:19:24 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Rental;

use App\Actions\Market\HistoricOuterable\StoreHistoricOuterable;
use App\Actions\Market\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Product\ProductStateEnum;
use App\Enums\Market\Rental\RentalStateEnum;
use App\Models\Market\Rental;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRental extends OrgAction
{
    use WithActionUpdate;

    private Rental $rental;

    public function handle(Rental $rental, array $modelData): Rental
    {
        $productData = Arr::only($modelData, ['code', 'name', 'main_outerable_price', 'description', 'data', 'settings', 'status']);

        if(Arr::has($modelData, 'state')) {
            $productData['state']=match($modelData['state']) {
                RentalStateEnum::ACTIVE       => ProductStateEnum::ACTIVE,
                RentalStateEnum::DISCONTINUED => ProductStateEnum::DISCONTINUED,
                RentalStateEnum::IN_PROCESS   => ProductStateEnum::IN_PROCESS,
            };

        }

        $product= $rental->product;
        $this->update($product, $productData);
        $product->refresh();

        $rental= $this->update($rental, Arr::except($modelData, ['code', 'name', 'main_outerable_price', 'description', 'data', 'settings']));

        $changed=$product->getChanges();

        if(Arr::hasAny($changed, ['name', 'code', 'main_outerable_price'])) {

            $historicOuterable = StoreHistoricOuterable::run($rental);
            $product->update(
                [
                    'current_historic_outerable_id' => $historicOuterable->id,
                ]
            );
        }
        if(Arr::hasAny($rental->getChanges(), ['state'])) {
            ShopHydrateRentals::dispatch($rental->shop);
        }
        return $rental;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
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
                        ['column' => 'id', 'value' => $this->rental->product->id, 'operator' => '!=']
                    ]
                ),
            ],
            'name'                       => ['sometimes', 'required', 'max:250', 'string'],
            'main_outerable_price'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'description'                => ['sometimes', 'required', 'max:1500'],
            'data'                       => ['sometimes', 'array'],
            'settings'                   => ['sometimes', 'array'],
            'status'                     => ['sometimes','required','boolean'],
            'state'                      => ['sometimes','required',Rule::enum(RentalStateEnum::class)],
            'auto_assign_asset'          => ['nullable','string','in:Pallet,StoredItem'],
            'auto_assign_asset_type'     => ['nullable','string','in:pallet,box,oversize'],
        ];
    }

    public function asController(Rental $rental, ActionRequest $request): Rental
    {
        $this->rental        =$rental;

        $this->initialisationFromShop($rental->shop, $request);
        return $this->handle($rental, $this->validatedData);
    }

    public function action(Rental $rental, array $modelData, int $hydratorsDelay = 0): Rental
    {
        $this->asAction       = true;
        $this->rental         =$rental;

        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($rental->shop, $modelData);
        return $this->handle($rental, $this->validatedData);
    }


}
