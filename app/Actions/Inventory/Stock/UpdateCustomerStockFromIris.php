<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 24 Nov 2022 13:43:05 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\fromIris;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Stock;
use App\Models\Web\WebUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;


class UpdateCustomerStockFromIris extends fromIris
{

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            [
                'stock_id' => ['required', 'integer'],
                'code' => ['sometimes', 'required', 'alpha_dash'],
                'description' => ['sometimes', 'nullable', 'string', 'max:10000']
            ]

        );
    }


    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(WebUser $webUser, array $modelData): ?Stock
    {
        $stock = Stock::findOrFail(Arr::get($modelData, 'stock_id'));

        if (!($stock->owner_type == 'Customer' and $stock->owner_id == $webUser->customer->id)) {
            throw new AuthorizationException();
        }


        if ($webUser->customer->stocks()->where('code', Arr::get($modelData, 'code'))->where('id', '!=', $stock->id)->exists()) {
            throw  ValidationException::withMessages([
                                                         'code' => 'Stock code used in another stock'
                                                     ]);
        }

        return StoreStock::run($webUser->customer, Arr::except($modelData,'stock_id'));
    }

    public function jsonResponse(?Stock $stock): StockResource
    {
        if (!$stock) {
            abort(500, 'Could not update stock');
        }

        return new StockResource($stock);
    }


}

